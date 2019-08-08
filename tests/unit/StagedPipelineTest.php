<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Unit;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NicWortel\CommandPipeline\Stage;
use NicWortel\CommandPipeline\StagedPipeline;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use stdClass;

class StagedPipelineTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testPassesCommandsToStage(): void
    {
        $command = new stdClass();
        $stage = Mockery::spy(Stage::class);

        $pipeline = new StagedPipeline([$stage], new NullLogger());

        $pipeline->process($command);

        $stage->shouldHaveReceived('process', [$command]);
    }

    public function testPassesCommandsThroughMultipleStagesInCorrectOrder(): void
    {
        $command = new stdClass();
        $stage1 = Mockery::mock(Stage::class)
            ->shouldReceive('process')
            ->with($command)
            ->andReturn($command)
            ->globally()
            ->ordered()
            ->getMock();
        $stage2 = Mockery::mock(Stage::class)
            ->shouldReceive('process')
            ->with($command)
            ->andReturn($command)
            ->globally()
            ->ordered()
            ->getMock();

        $pipeline = new StagedPipeline([$stage1, $stage2], new NullLogger());

        $pipeline->process($command);
    }

    public function testPassesTheOutputOfThePreviousStageToTheNext(): void
    {
        $command = new stdClass();
        $stage1 = Mockery::mock(Stage::class)->shouldReceive('process')->andReturn($command)->getMock();
        $stage2 = Mockery::spy(Stage::class);

        $pipeline = new StagedPipeline([$stage1, $stage2], new NullLogger());

        $pipeline->process(new stdClass());

        $stage2->shouldHaveReceived('process', [$command]);
    }

    public function testLogsWhenStartingAndFinishingHandlingACommand(): void
    {
        $command = new stdClass();

        $stage = Mockery::mock(Stage::class)->shouldReceive('process')->andReturn($command)->getMock();
        $logger = Mockery::spy(LoggerInterface::class);
        $pipeline = new StagedPipeline([$stage], $logger);

        $pipeline->process($command);

        $logger->shouldHaveReceived('debug', ['Started processing a command', ['command' => stdClass::class]]);
        $logger->shouldHaveReceived('debug', ['Finished processing a command', ['command' => stdClass::class]]);
    }
}
