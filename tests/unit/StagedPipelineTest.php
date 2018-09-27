<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Unit;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NicWortel\CommandPipeline\Stage;
use NicWortel\CommandPipeline\StagedPipeline;
use PHPUnit\Framework\TestCase;
use stdClass;

class StagedPipelineTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testThatACommandIsPassedToAStage(): void
    {
        $command = new stdClass();
        $stage = Mockery::spy(Stage::class);

        $pipeline = new StagedPipeline([$stage]);

        $pipeline->process($command);

        $stage->shouldHaveReceived('process', [$command]);
    }

    public function testThatItPassesACommandThroughMultipleStagesInTheCorrectOrder(): void
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

        $pipeline = new StagedPipeline([$stage1, $stage2]);

        $pipeline->process($command);
    }

    public function testThatItPassesTheOutputOfThePreviousStageToTheNext(): void
    {
        $command = new stdClass();
        $stage1 = Mockery::mock(Stage::class)->shouldReceive('process')->andReturn($command)->getMock();
        $stage2 = Mockery::spy(Stage::class);

        $pipeline = new StagedPipeline([$stage1, $stage2]);

        $pipeline->process(new stdClass());

        $stage2->shouldHaveReceived('process', [$command]);
    }
}
