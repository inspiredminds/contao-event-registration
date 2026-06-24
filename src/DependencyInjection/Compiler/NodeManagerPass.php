<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Terminal42\NodeBundle\NodeManager;

/**
 * Compatibility with terminal42/contao-node 1.x.
 */
class NodeManagerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has(NodeManager::class) || !$container->has('terminal42_node.manager')) {
            return;
        }

        $container->setAlias(NodeManager::class, 'terminal42_node.manager')->setPublic(true);
    }
}
