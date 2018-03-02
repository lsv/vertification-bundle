<?php

namespace Lsv\Vertification\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class HandlerCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $serviceName = 'lsv_vertification.type.handler';
        $taggedServiceName = 'lsv_vertification.type.add';

        if (!$container->hasDefinition($serviceName)) {
            return;
        }

        $definition = $container->getDefinition($serviceName);
        foreach ($container->findTaggedServiceIds($taggedServiceName) as $id => $attributes) {
            $definition->addMethodCall('addType', [
                new Reference($id),
            ]);
        }
    }
}
