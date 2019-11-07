<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Form\Type;

use Dmytrof\AccessPermissionsBundle\Form\DataTransformer\AccessAttributeToStringTransformer;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\{AbstractType,
    CallbackTransformer,
    Extension\Core\Type\ChoiceType,
    FormBuilderInterface};

class AccessAttributesChoiceType extends AbstractType
{
    /**
     * @var AccessAttributeToStringTransformer
     */
    protected $transformer;

    /**
     * AccessAttributesChoiceType constructor.
     * @param AccessAttributeToStringTransformer $transformer
     */
    public function __construct(AccessAttributeToStringTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->transformer->getAttributes(),
            'label' => 'label.security.access_attributes',
            'multiple' => true,
            'by_reference' => false,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->resetViewTransformers()
            ->resetModelTransformers();

        $transformer = $this->transformer;
        $builder->addViewTransformer(new CallbackTransformer(function ($value) {
            return $value;
        }, function ($value) use ($transformer) {
            $collection = new ArrayCollection();
            if (is_array($value)) {
                foreach ($value as $val) {
                    $entity = $transformer->reverseTransform($val);
                    if ($entity) {
                        $collection->add($entity);
                    }
                }
            }
            return $collection;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}