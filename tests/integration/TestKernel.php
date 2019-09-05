<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use NicWortel\CommandPipeline\Bundle\CommandPipelineBundle;
use SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    public function __construct()
    {
        parent::__construct('test', true);
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new SimpleBusCommandBusBundle(),
            new CommandPipelineBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(
            function (ContainerBuilder $container): void {
                $container->setParameter('kernel.secret', 'foo');

                $container->loadFromExtension(
                    'doctrine',
                    [
                        'dbal' => [
                            'connections' => [
                                'default' => [
                                    'url' => 'mysql://user:password@127.0.0.1:3306/test?charset=utf8mb4&serverVersion=5.7',
                                ],
                            ],
                        ],
                        'orm' => [
                            'entity_managers' => [
                                'default' => [

                                ],
                            ],
                        ],
                    ]
                );
            }
        );
    }
}
