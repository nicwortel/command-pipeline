<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline;

use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;

final class CommandHandlingStage implements Stage
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(MessageBus $commandBus, LoggerInterface $logger)
    {
        $this->commandBus = $commandBus;
        $this->logger = $logger;
    }

    public function process(object $command): object
    {
        $this->commandBus->handle($command);

        $this->logger->debug('The command has been handled by its command handler.');

        return $command;
    }
}
