<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\System;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use NicWortel\CommandPipeline\Tests\Integration\Validation\CommandStub;
use NicWortel\CommandPipeline\Tests\System\Entity\TestEntity;
use NicWortel\CommandPipeline\Validation\InvalidCommandException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class CommandPipelineTest extends TestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createKernel()->getContainer()->get('doctrine.orm.entity_manager');

        $schemaTool = new SchemaTool($this->entityManager);

        $classes = [$this->entityManager->getClassMetadata(TestEntity::class)];

        $schemaTool->dropSchema($classes);
        $schemaTool->updateSchema($classes);
    }

    public function testFailsIfTheCommandIsInvalid(): void
    {
        $kernel = $this->createKernel();
        $commandPipeline = $kernel->getContainer()->get('command_pipeline');

        $command = new CommandStub();
        $command->emailAddress = 'foo';

        $this->expectException(InvalidCommandException::class);

        $commandPipeline->process($command);
    }

    public function testPassesTheCommandToTheCommandHandler(): void
    {
        $kernel = $this->createKernel();
        $container = $kernel->getContainer();
        $commandPipeline = $container->get('command_pipeline');

        $command = new CommandStub();
        $command->emailAddress = 'info@example.com';

        $commandPipeline->process($command);

        /** @var TestEntity[] $entities */
        $entities = $this->entityManager->getRepository(TestEntity::class)->findAll();

        $this->assertCount(1, $entities);
        $this->assertSame('info@example.com', $entities[0]->emailAddress);
    }

    public function testPublishesEventsAfterHandlingTheCommand(): void
    {
        $kernel = $this->createKernel();
        $container = $kernel->getContainer();
        $commandPipeline = $container->get('command_pipeline');

        $command = new CommandStub();
        $command->emailAddress = 'info@example.com';

        $commandPipeline->process($command);

        /** @var EventSubscriberStub $eventSubscriber */
        $eventSubscriber = $container->get('event_subscriber');

        $this->assertInstanceOf(DummyEvent::class, $eventSubscriber->getEvent());
    }

    private function createKernel(): KernelInterface
    {
        $kernel = new TestKernel();
        $kernel->boot();

        return $kernel;
    }
}
