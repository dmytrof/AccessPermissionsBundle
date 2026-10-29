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

use Dmytrof\AccessPermissionsBundle\Form\DataTransformer\AccessAttributeToStringTransformer;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\{AbstractType, CallbackTransformer, Extension\Core\Type\ChoiceType, FormBuilderInterface};
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Choice of access attributes: submitted attributes are transformed to collection of access attribute entities
 */
class AccessAttributesChoiceType extends AbstractType
{
    public function __construct(
        protected AccessAttributeToStringTransformer $transformer,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->transformer->getAttributes(),
            'label' => 'label.security.access_attributes',
            'multiple' => true,
            'by_reference' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->resetViewTransformers()
            ->resetModelTransformers();

        $transformer = $this->transformer;
        $builder->addViewTransformer(new CallbackTransformer(
            static fn (mixed $value): mixed => $value,
            static function (mixed $value) use ($transformer): ArrayCollection {
                $collection = new ArrayCollection();
                foreach (is_array($value) ? $value : [] as $attribute) {
                    if ($entity = $transformer->reverseTransform($attribute)) {
                        $collection->add($entity);
                    }
                }

                return $collection;
            },
        ));
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
