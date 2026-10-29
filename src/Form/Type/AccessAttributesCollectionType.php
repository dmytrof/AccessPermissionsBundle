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

namespace Dmytrof\AccessPermissionsBundle\Form\Type;

use Symfony\Component\Form\{AbstractType, Extension\Core\Type\CollectionType};
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Collection of AccessAttributeType
 */
class AccessAttributesCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'entry_type' => AccessAttributeType::class,
        ]);
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}
