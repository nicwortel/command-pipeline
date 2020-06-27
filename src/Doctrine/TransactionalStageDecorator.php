<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use NicWortel\CommandPipeline\Stage;
use Psr\Log\LoggerInterface;
use Throwable;

use function get_class;

final class TransactionalStageDecorator implements Stage
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Stage
     */
    private $wrappedStage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, Stage $wrappedStage, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->wrappedStage = $wrappedStage;
        $this->logger = $logger;
    }

    public function process(object $command): object
    {
        $this->logger->debug('Starting a new database transaction.');

        $this->entityManager->beginTransaction();

        try {
            $command = $this->wrappedStage->process($command);

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->logger->debug('Flushed the entity manager and committed the database transaction.');
        } catch (Throwable $error) {
            $this->logger->error(
                'An exception was thrown while handling the command. Rolling back the transaction.',
                [
                    'command' => get_class($command),
                    'error' => $error->getMessage(),
                ]
            );

            $this->entityManager->rollback();

            throw $error;
        }

        return $command;
    }
}
