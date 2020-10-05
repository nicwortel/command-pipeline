<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline\EventHandling;

use NicWortel\CommandPipeline\Stage;
use Psr\Log\LoggerInterface;

final class EventDispatchingStage implements Stage
{
    private BufferedEventBus $bufferedEventBus;

    private LoggerInterface $logger;

    public function __construct(BufferedEventBus $bufferedEventBus, LoggerInterface $logger)
    {
        $this->bufferedEventBus = $bufferedEventBus;
        $this->logger = $logger;
    }

    public function process(object $command): object
    {
        $this->bufferedEventBus->flush();

        $this->logger->debug('Dispatched events that were triggered by handling the command.');

        return $command;
    }
}
