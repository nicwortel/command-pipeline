<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Unit\Bundle\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use NicWortel\CommandPipeline\Bundle\DependencyInjection\CommandPipelineExtension;
use NicWortel\CommandPipeline\Doctrine\TransactionalPipelineDecorator;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;

class CommandPipelineExtensionTest extends AbstractExtensionTestCase
{
    public function testLoadsTheCommandPipelineWithDefaultConfiguration(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService('command_pipeline', TransactionalPipelineDecorator::class);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'nicwortel.command_pipeline.transactional_decorator',
            0,
            new Reference('doctrine.orm.entity_manager')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'nicwortel.command_pipeline.transactional_decorator',
            2,
            new Reference('logger')
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'nicwortel.command_pipeline.default',
            0,
            [
                new Reference('nicwortel.command_pipeline.stage.validation'),
                new Reference('nicwortel.command_pipeline.stage.command_handling'),
            ]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'nicwortel.command_pipeline.default',
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
