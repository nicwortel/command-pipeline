<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Integration;

use NicWortel\CommandPipeline\StagedPipeline;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testContainer(): void
    {
        $kernel = new TestKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $commandPipeline = $container->get('command_pipeline');

        $this->assertInstanceOf(StagedPipeline::class, $commandPipeline);
    }
}
