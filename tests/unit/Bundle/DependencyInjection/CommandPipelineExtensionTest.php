<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Unit\Bundle\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use NicWortel\CommandPipeline\Bundle\DependencyInjection\CommandPipelineExtension;
use NicWortel\CommandPipeline\StagedPipeline;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;

class CommandPipelineExtensionTest extends AbstractExtensionTestCase
{
    public function testLoadsTheCommandPipelineWithDefaultConfiguration(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService('command_pipeline', StagedPipeline::class);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'nicwortel.command_pipeline',
            0,
            [
                new Reference('nicwortel.command_pipeline.stage.validation'),
                new Reference('nicwortel.command_pipeline.stage.transactional_decorator'),
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'nicwortel.command_pipeline',
            1,
            new Reference('logger')
        );
    }

    /**
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions(): array
    {
        return [new CommandPipelineExtension()];
    }
}
