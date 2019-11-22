<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Service;

use Doctrine\Common\Collections\{
	Collection, ArrayCollection
};

use Dmytrof\AccessPermissionsBundle\{Exception\VoterException,
    Model\AttributeDescription,
    Model\AttributeDescriptionsCollection,
    Security\AbstractVoter,
    Security\ModuleVoterInterface};
use Symfony\Contracts\Translation\TranslatorInterface;

class VotersContainer implements \IteratorAggregate
{
    /**
     * @var Collection|AbstractVoter[]
     */
	protected $voters;

    /**
     * @var TranslatorInterface
     */
	protected $translator;

    /**
     * @var string
     */
	protected $translationDomain;

    /**
     * @var AttributeDescriptionsCollection
     */
	protected $attributeDescriptionsCollection;

    /**
     * VotersContainer constructor.
     * @param iterable $voters
     * @param TranslatorInterface $translator
     * @param string|null $translationDomain
     */
    public function __construct(iterable $voters, TranslatorInterface $translator, ?string $translationDomain = null)
    {
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->voters = new ArrayCollection();
        foreach ($voters as $voter) {
            $this->addVoter($voter);
        }
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * Returns translation domain
     * @return string|null
     */
    protected function getTranslationDomain(): ?string
    {
        return $this->translationDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->voters->getIterator();
    }

    /**
     * Adds voter
     * @param AbstractVoter $voter
     * @return VotersContainer
     */
    public function addVoter(AbstractVoter $voter): self
    {
        if (!$this->voters->contains($voter)) {
            $this->voters->add($voter);
            $this->attributeDescriptionsCollection = null;
        }
        return $this;
    }

    /**
     * Returns all attributes
     * @return array
     */
    public function getAllAttributes(): array
    {
        return $this->getVotersAttributes($this->voters);
    }

    /**
     * Returns attribute descriptions collection
     * @return AttributeDescriptionsCollection
     */
    public function getAttributeDescriptionsCollection(): AttributeDescriptionsCollection
    {
        if (is_null($this->attributeDescriptionsCollection)) {
            $this->attributeDescriptionsCollection = $this->getAttributeDescriptions($this->voters);
        }
        return $this->attributeDescriptionsCollection;
    }

    /**
     * Returns voters attributes
     * @param iterable $voters
     * @return array
     */
    public function getVotersAttributes(iterable $voters): array
    {
        $attributes = [];
        /** @var AbstractVoter $voter */
        foreach ($voters as $voter) {
            $attributes = array_merge($attributes, array_combine($voter->getAttributes(), $voter->getAttributes()));
        }
        return $attributes;
    }

    /**
     * Returns attribute descriptions for voters
     * @param iterable $voters
     * @return AttributeDescriptionsCollection
     */
    public function getAttributeDescriptions(iterable $voters): AttributeDescriptionsCollection
    {
        $collection = new AttributeDescriptionsCollection();
        foreach ($voters as $voter) {
            foreach ($voter->getAttributes() as $attribute) {
                $collection->add(new AttributeDescription($attribute, ($voter instanceof ModuleVoterInterface), $this->getTranslator(), $this->getTranslationDomain()));
            }
        }
        return $collection->sort();
    }

    /**
     * Returns module attributes descriptions
     * @param string $moduleCode
     * @return AttributeDescriptionsCollection
     */
    public function getModuleAttributeDescriptions(string $moduleCode): AttributeDescriptionsCollection
    {
        $voters = [];
        foreach ($this->voters as $voter) {
            if ($voter instanceof ModuleVoterInterface && $voter->getModule() === $moduleCode) {
                array_push($voters, $voter);
            }
        }
        return $this->getAttributeDescriptions($voters);
    }
}