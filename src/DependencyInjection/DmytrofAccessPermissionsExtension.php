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

use Dmytrof\AccessPermissionsBundle\Security\AbstractVoter;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Loader};
use Dmytrof\AccessPermissionsBundle\Service\VotersContainer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DmytrofAccessPermissionsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(AbstractVoter::class)
            ->addTag('dmytrof.access_permissions.voter');

        $votersContainerDefinition = $container->getDefinition(VotersContainer::class);
        $votersContainerDefinition->setArgument('$translationDomain', $config['translation_domain']);
    }
}
