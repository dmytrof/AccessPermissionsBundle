<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Form\Type;

use Dmytrof\AccessPermissionsBundle\Entity\AccessAttribute\AccessAttribute;
use Dmytrof\AccessPermissionsBundle\Form\DataTransformer\AccessAttributeToStringTransformer;
use Symfony\Component\Form\{AbstractType, FormBuilderInterface, Extension\Core\Type\TextType};
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessAttributeType extends AbstractType
{
    /**
     * @var AccessAttributeToStringTransformer
     */
    protected $transformer;

    /**
     * AccessAttributeType constructor.
     * @param AccessAttributeToStringTransformer $transformer
     */
    public function __construct(AccessAttributeToStringTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AccessAttribute::class,
            'csrf_protection' => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->transformer->setEntityClass($options['data_class']));
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return TextType::class;
    }
}