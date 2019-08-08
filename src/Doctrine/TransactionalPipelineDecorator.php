<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use NicWortel\CommandPipeline\CommandPipeline;
use Psr\Log\LoggerInterface;
use Throwable;
use function get_class;

final class TransactionalPipelineDecorator implements CommandPipeline
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CommandPipeline
     */
    private $innerPipeline;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommandPipeline $innerPipeline,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->innerPipeline = $innerPipeline;
        $this->logger = $logger;
    }

    public function process(object $command): void
    {
        $this->entityManager->beginTransaction();

        try {
            $this->innerPipeline->process($command);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $error) {
            $this->logger->error(
                'An exception was thrown while handling the command, rolling back the transaction',
                [
                    'command' => get_class($command),
                    'error' => $error->getMessage(),
                ]
            );

            $this->entityManager->rollback();

            throw $error;
        }
    }
}
