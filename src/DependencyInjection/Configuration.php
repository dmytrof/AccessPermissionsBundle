<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dmytrof\AccessPermissionsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const ROOT_NAME = 'dmytrof_access_permissions';
    public const DEFAULT_TRANSLATION_DOMAIN = 'access_attributes';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NAME);

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->scalarNode('translation_domain')
                        ->info('Translation domain of access attributes labels and descriptions')
                        ->defaultValue(self::DEFAULT_TRANSLATION_DOMAIN)
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
