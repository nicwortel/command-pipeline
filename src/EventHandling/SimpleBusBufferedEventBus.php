<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\EventHandling;

use SimpleBus\Message\Bus\MessageBus;

final class SimpleBusBufferedEventBus implements MessageBus, BufferedEventBus
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var object[]
     */
    private $buffer = [];

    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @param object $message
     */
    public function handle($message): void
    {
        $this->buffer[] = $message;
    }

    public function flush(): void
    {
        foreach ($this->buffer as $event) {
            $this->eventBus->handle($event);
        }
    }
}
