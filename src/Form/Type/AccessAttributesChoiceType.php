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

use Dmytrof\AccessPermissionsBundle\Service\VotersContainer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\{AbstractType, Extension\Core\Type\ChoiceType, FormBuilderInterface};

class AccessAttributesChoiceType extends AbstractType
{
    /**
     * @var VotersContainer
     */
    protected $votersContainer;

    /**
     * SecurityAccessAttributesType constructor.
     * @param VotersContainer $votersContainer
     */
    public function __construct(VotersContainer $votersContainer)
    {
        $this->votersContainer = $votersContainer;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->votersContainer->getAllAttributes(),
            'label' => 'label.security.access_attributes',
            'multiple' => true,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->resetViewTransformers();
        $builder->resetModelTransformers();
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}