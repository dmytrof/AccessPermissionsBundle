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

use Dmytrof\AccessPermissionsBundle\Entity\AccessAttribute\AccessAttribute;
use Dmytrof\AccessPermissionsBundle\Form\DataTransformer\AccessAttributeToStringTransformer;
use Symfony\Component\Form\{AbstractType, Extension\Core\Type\TextType, FormBuilderInterface};
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Access attribute entity as text field with the attribute
 */
class AccessAttributeType extends AbstractType
{
    public function __construct(
        protected AccessAttributeToStringTransformer $transformer,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AccessAttribute::class,
            'csrf_protection' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // The transformer service is shared: each form gets a copy with its own entity class
        $builder->addViewTransformer((clone $this->transformer)->setEntityClass($options['data_class']));
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
