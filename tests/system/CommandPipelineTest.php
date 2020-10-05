<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\System;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use NicWortel\CommandPipeline\Authorization\ForbiddenException;
use NicWortel\CommandPipeline\Tests\System\Entity\TestEntity;
use NicWortel\CommandPipeline\Validation\InvalidCommandException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CommandPipelineTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    private TestKernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = new TestKernel();
        $this->kernel->boot();

        $this->entityManager = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $schemaTool = new SchemaTool($this->entityManager);

        $classes = [$this->entityManager->getClassMetadata(TestEntity::class)];

        $schemaTool->dropSchema($classes);
        $schemaTool->updateSchema($classes);
    }

    public function testFailsIfTheCommandIsInvalid(): void
    {
        $commandPipeline = $this->kernel->getContainer()->get('command_pipeline');

        $command = new CommandStub();
        $command->emailAddress = 'foo';

        $this->expectException(InvalidCommandException::class);

        $commandPipeline->process($command);
    }

    public function testFailsIfTheUserIsNotAuthorizedToExecuteTheCommand(): void
    {
        $commandPipeline = $this->kernel->getContainer()->get('command_pipeline');

        $this->authenticateAsUserWithRoles(['ROLE_USER'], $this->kernel->getContainer()->get('security.token_storage'));

        $command = new CommandStub();
        $command->emailAddress = 'info@example.com';

        $this->expectException(ForbiddenException::class);

        $commandPipeline->process($command);
    }

    public function testPassesAValidAndAuthorizedCommandToTheCommandHandler(): void
    {
        $container = $this->kernel->getContainer();
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
        $container = $this->kernel->getContainer();
        $commandPipeline = $container->get('command_pipeline');

        $this->authenticateAsUserWithRoles(['ROLE_ADMIN'], $container->get('security.token_storage'));

        $command = new CommandStub();
        $command->emailAddress = 'info@example.com';

        $commandPipeline->process($command);

        /** @var EventSubscriberStub $eventSubscriber */
        $eventSubscriber = $container->get('event_subscriber');

        $this->assertInstanceOf(DummyEvent::class, $eventSubscriber->getEvent());
    }

    /**
     * @param string[] $roles
     */
    private function authenticateAsUserWithRoles(array $roles, ?TokenStorage $tokenStorage): void
    {
        $tokenStorage->setToken(new UsernamePasswordToken('user', [], 'provider', $roles));
    }
}
