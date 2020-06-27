<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Integration;

use NicWortel\CommandPipeline\EventHandling\SimpleBusBufferedEventBus;
use NicWortel\CommandPipeline\StagedPipeline;
use NicWortel\CommandPipeline\Tests\System\TestKernel;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testRegistersContainerServices(): void
    {
        $kernel = new TestKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $commandPipeline = $container->get('command_pipeline');
        $this->assertInstanceOf(StagedPipeline::class, $commandPipeline);

        $eventBus = $container->get('buffered_event_bus');
        $this->assertInstanceOf(SimpleBusBufferedEventBus::class, $eventBus);
    }
}
