<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Unit\EventHandling;

use Mockery;
use NicWortel\CommandPipeline\EventHandling\SimpleBusBufferedEventBus;
use PHPUnit\Framework\TestCase;
use SimpleBus\Message\Bus\MessageBus;
use stdClass;

class SimpleBusBufferedEventBusTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var Mockery\MockInterface|MessageBus
     */
    private $innerEventBus;

    protected function setUp(): void
    {
        $this->innerEventBus = Mockery::spy(MessageBus::class);
    }

    public function testBuffersEvents(): void
    {
        $bufferedEventBus = new SimpleBusBufferedEventBus($this->innerEventBus);

        $event = new stdClass();

        $bufferedEventBus->handle($event);

        $this->innerEventBus->shouldNotHaveBeenCalled();
    }

    public function testFlushesEvents(): void
    {
        $bufferedEventBus = new SimpleBusBufferedEventBus($this->innerEventBus);

        $event = new stdClass();

        $bufferedEventBus->handle($event);
        $bufferedEventBus->flush();

        $this->innerEventBus->shouldHaveReceived('handle', [$event]);
    }

    public function testHandlesEventsInTheOrderTheyWerePassedToIt(): void
    {
        $bufferedEventBus = new SimpleBusBufferedEventBus($this->innerEventBus);

        $event1 = new stdClass();
        $event2 = new stdClass();

        $this->innerEventBus->shouldReceive('handle')->once()->with($event1)->ordered();
        $this->innerEventBus->shouldReceive('handle')->once()->with($event2)->ordered();

        $bufferedEventBus->handle($event1);
        $bufferedEventBus->handle($event2);
        $bufferedEventBus->flush();
    }

    public function testDiscardsEventsAfterFlushing(): void
    {
        $bufferedEventBus = new SimpleBusBufferedEventBus($this->innerEventBus);

        $event = new stdClass();

        $bufferedEventBus->handle($event);
        $bufferedEventBus->flush();

        $bufferedEventBus->flush();

        $this->innerEventBus->shouldHaveReceived('handle', [$event])->once();
    }
}
