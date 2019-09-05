<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\System;

use Doctrine\ORM\EntityManagerInterface;
use NicWortel\CommandPipeline\Tests\Integration\Validation\CommandStub;
use NicWortel\CommandPipeline\Tests\System\Entity\TestEntity;

final class CommandHandlerStub
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handle(CommandStub $command): void
    {
        $entity = new TestEntity();
        $entity->emailAddress = $command->emailAddress;

        $this->entityManager->persist($entity);
    }
}
