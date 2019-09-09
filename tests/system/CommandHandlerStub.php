<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\System;

use Doctrine\ORM\EntityManagerInterface;
use NicWortel\CommandPipeline\Tests\Integration\Validation\CommandStub;
use NicWortel\CommandPipeline\Tests\System\Entity\TestEntity;
use SimpleBus\Message\Bus\MessageBus;

final class CommandHandlerStub
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MessageBus
     */
    private $eventBus;

    public function __construct(EntityManagerInterface $entityManager, MessageBus $eventBus)
    {
        $this->entityManager = $entityManager;
        $this->eventBus = $eventBus;
    }

    public function handle(CommandStub $command): void
    {
        $entity = new TestEntity();
        $entity->emailAddress = $command->emailAddress;

        $this->entityManager->persist($entity);

        $event = new DummyEvent();

        $this->eventBus->handle($event);
    }
}
