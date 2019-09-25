<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\System;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use NicWortel\CommandPipeline\Authorization\ForbiddenException;
use NicWortel\CommandPipeline\Tests\System\Entity\TestEntity;
use NicWortel\CommandPipeline\Validation\InvalidCommandException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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

    public function testFailsIfTheUserIsNotAuthorizedToExecuteTheCommand(): void
    {
        $kernel = $this->createKernel();
        $commandPipeline = $kernel->getContainer()->get('command_pipeline');

        $this->authenticateAsUserWithRoles(['ROLE_USER'], $kernel->getContainer()->get('security.token_storage'));

        $command = new CommandStub();
        $command->emailAddress = 'info@example.com';

        $this->expectException(ForbiddenException::class);

        $commandPipeline->process($command);
    }

    public function testPassesAValidAndAuthorizedCommandToTheCommandHandler(): void
    {
        $kernel = $this->createKernel();
        $container = $kernel->getContainer();
        $commandPipeline = $container->get('command_pipeline');

        $this->authenticateAsUserWithRoles(['ROLE_ADMIN'], $container->get('security.token_storage'));

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

        $this->authenticateAsUserWithRoles(['ROLE_ADMIN'], $container->get('security.token_storage'));

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

    /**
     * @param string[] $roles
     */
    private function authenticateAsUserWithRoles(array $roles, ?TokenStorage $tokenStorage): void
    {
        $tokenStorage->setToken(new UsernamePasswordToken('user', [], 'provider', $roles));
    }
}
