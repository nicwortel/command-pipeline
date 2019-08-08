<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline;

use SimpleBus\Message\Bus\MessageBus;

final class CommandHandlingStage implements Stage
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function process(object $command): object
    {
        $this->commandBus->handle($command);

        return $command;
    }
}
