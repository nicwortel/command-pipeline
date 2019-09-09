<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\System;

final class EventSubscriberStub
{
    /**
     * @var object|null
     */
    private $event;

    public function handle(DummyEvent $event): void
    {
        $this->event = $event;
    }

    public function getEvent(): ?object
    {
        return $this->event;
    }
}
