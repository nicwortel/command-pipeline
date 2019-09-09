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
}
