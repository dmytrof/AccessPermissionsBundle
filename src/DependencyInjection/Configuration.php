<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\{
    Builder\TreeBuilder, ConfigurationInterface
};

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('dmytrof_access_permissions');

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->scalarNode('translation_domain')->defaultValue('access_attributes')->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
