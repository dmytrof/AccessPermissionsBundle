<?php

/*
 * This file is part of the DmytrofFractalBundle package.
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
     * @var AttributeDescriptionsCollection
     */
	protected $attributeDescriptionsCollection;

    /**
     * SecurityVotersContainer constructor.
     * @param TranslatorInterface $translator
     * @param iterable $voters
     */
    public function __construct(TranslatorInterface $translator, iterable $voters)
    {
        $this->translator = $translator;
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
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->voters->toArray());
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
        foreach ($this->getVotersAttributes($voters) as $attribute) {
            $collection->add(new AttributeDescription($attribute, $this->getTranslator()));
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