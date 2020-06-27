<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline\EventHandling;

use SimpleBus\Message\Bus\MessageBus;

use function array_shift;

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
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function handle($message): void
    {
        $this->buffer[] = $message;
    }

    public function flush(): void
    {
        while ($event = array_shift($this->buffer)) {
            $this->eventBus->handle($event);
        }
    }
}
