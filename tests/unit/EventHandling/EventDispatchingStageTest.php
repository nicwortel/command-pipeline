<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Unit\EventHandling;

use Mockery;
use NicWortel\CommandPipeline\EventHandling\BufferedEventBus;
use NicWortel\CommandPipeline\EventHandling\EventDispatchingStage;
use NicWortel\CommandPipeline\Tests\Integration\Validation\CommandStub;
use PHPUnit\Framework\TestCase;

class EventDispatchingStageTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testFlushesBufferedEventBus(): void
    {
        $eventBus = Mockery::spy(BufferedEventBus::class);

        $stage = new EventDispatchingStage($eventBus);

        $command = new CommandStub();

        $result = $stage->process($command);

        $this->assertSame($command, $result);
        $eventBus->shouldHaveReceived('flush');
    }
}
