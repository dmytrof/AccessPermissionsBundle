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

use Dmytrof\AccessPermissionsBundle\Security\AbstractVoter;
use Dmytrof\AccessPermissionsBundle\Service\VotersContainer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class DmytrofAccessPermissionsExtension extends Extension
{
    public const VOTER_TAG = 'dmytrof.access_permissions.voter';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $container->registerForAutoconfiguration(AbstractVoter::class)
            ->addTag(self::VOTER_TAG);

        $container->getDefinition(VotersContainer::class)
            ->setArgument('$translationDomain', $config['translation_domain']);
    }

    public function getAlias(): string
    {
        return Configuration::ROOT_NAME;
    }
}
