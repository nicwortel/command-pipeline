<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\EventHandling;

use NicWortel\CommandPipeline\Stage;

final class EventDispatchingStage implements Stage
{
    /**
     * @var BufferedEventBus
     */
    private $bufferedEventBus;

    public function __construct(BufferedEventBus $bufferedEventBus)
    {
        $this->bufferedEventBus = $bufferedEventBus;
    }

    public function process(object $command): object
    {
        $this->bufferedEventBus->flush();

        return $command;
    }
}
