<?php
declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\System;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use NicWortel\CommandPipeline\Bundle\CommandPipelineBundle;
use Psr\Log\NullLogger;
use SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle;
use SimpleBus\SymfonyBridge\SimpleBusEventBusBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use function getenv;

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
            new SecurityBundle(),
            new FrameworkBundle(),
            new DoctrineBundle(),
            new SimpleBusCommandBusBundle(),
            new SimpleBusEventBusBundle(),
            new CommandPipelineBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(
            function (ContainerBuilder $container): void {
                $container->setParameter('kernel.secret', 'foo');

                $container->register('logger', NullLogger::class);

                $container->register('command_handler', CommandHandlerStub::class)
                    ->setPublic(true)
                    ->addArgument(new Reference('doctrine.orm.entity_manager'))
                    ->addArgument(new Reference('event_bus'))
                    ->addTag('command_handler', ['handles' => CommandStub::class]);

                $container->register('event_subscriber', EventSubscriberStub::class)
                    ->setPublic(true)
                    ->addTag('event_subscriber', ['subscribes_to' => DummyEvent::class]);

                $container->loadFromExtension(
                    'doctrine',
                    [
                        'dbal' => [
                            'connections' => [
                                'default' => [
                                    'url' => getenv('DATABASE_URL'),
                                ],
                            ],
                        ],
                        'orm' => [
                            'entity_managers' => [
                                'default' => [
                                    'mappings' => [
                                        'default' => [
                                            'type' => 'annotation',
                                            'dir' => __DIR__ . '/../system/Entity/',
                                            'prefix' => 'NicWortel\CommandPipeline\Tests\System\Entity',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );

                $container->setParameter('request_listener.http_port', 80);
                $container->setParameter('request_listener.https_port', 443);

                $container->loadFromExtension(
                    'security',
                    [
                        'providers' => [
                            'in_memory' => [
                                'memory' => [],
                            ],
                        ],
                        'firewalls' => [
                            'stub' => [
                                'http_basic' => null,
                            ],
                        ],
                    ]
                );
            }
        );
    }
}
