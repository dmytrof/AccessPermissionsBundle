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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Dmytrof\AccessPermissionsBundle\DependencyInjection\DmytrofAccessPermissionsExtension;
use Dmytrof\AccessPermissionsBundle\Form\DataTransformer\AccessAttributeToStringTransformer;
use Dmytrof\AccessPermissionsBundle\Form\Type\{AccessAttributesChoiceType, AccessAttributesCollectionType, AccessAttributeType};
use Dmytrof\AccessPermissionsBundle\Service\{RolesContainer, VotersContainer};

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(VotersContainer::class)
        ->public()
        ->arg('$voters', tagged_iterator(DmytrofAccessPermissionsExtension::VOTER_TAG));

    $services->set(RolesContainer::class)
        ->public()
        ->arg('$systemRoles', param('security.role_hierarchy.roles'));

    $services->set(AccessAttributeToStringTransformer::class);

    $services->set(AccessAttributeType::class);
    $services->set(AccessAttributesChoiceType::class);
    $services->set(AccessAttributesCollectionType::class);
};
