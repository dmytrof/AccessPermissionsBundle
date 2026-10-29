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

namespace Dmytrof\AccessPermissionsBundle\Service;

use Dmytrof\AccessPermissionsBundle\{Model\AttributeDescription, Model\AttributeDescriptionsCollection, Security\AbstractVoter, Security\ModuleVoterInterface};
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Voters autoconfigured with "dmytrof.access_permissions.voter" tag (subclasses of AbstractVoter)
 *
 * @implements \IteratorAggregate<int, AbstractVoter>
 */
class VotersContainer implements \IteratorAggregate, \Countable
{
    /**
     * @var Collection<int, AbstractVoter>
     */
    protected Collection $voters;

    protected ?AttributeDescriptionsCollection $attributeDescriptionsCollection = null;

    /**
     * @param iterable<AbstractVoter> $voters
     */
    public function __construct(
        iterable $voters,
        protected TranslatorInterface $translator,
        protected ?string $translationDomain = null,
    ) {
        $this->voters = new ArrayCollection();
        foreach ($voters as $voter) {
            $this->addVoter($voter);
        }
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    protected function getTranslationDomain(): ?string
    {
        return $this->translationDomain;
    }

    /**
     * @return \Traversable<int, AbstractVoter>
     */
    public function getIterator(): \Traversable
    {
        return $this->voters->getIterator();
    }

    public function count(): int
    {
        return $this->voters->count();
    }

    public function addVoter(AbstractVoter $voter): static
    {
        if (!$this->voters->contains($voter)) {
            $this->voters->add($voter);
            $this->attributeDescriptionsCollection = null;
        }

        return $this;
    }

    /**
     * Returns attributes of all voters
     *
     * @return array<string, string>
     */
    public function getAllAttributes(): array
    {
        return $this->getVotersAttributes($this->voters);
    }

    /**
     * Returns attribute descriptions of all voters
     */
    public function getAttributeDescriptionsCollection(): AttributeDescriptionsCollection
    {
        return $this->attributeDescriptionsCollection ??= $this->getAttributeDescriptions($this->voters);
    }

    /**
     * Returns voters attributes
     *
     * @param iterable<AbstractVoter> $voters
     *
     * @return array<string, string>
     */
    public function getVotersAttributes(iterable $voters): array
    {
        $attributes = [];
        foreach ($voters as $voter) {
            foreach ($voter::getAttributes() as $attribute) {
                $attributes[$attribute] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Returns attribute descriptions of voters (sorted by attribute)
     *
     * @param iterable<AbstractVoter> $voters
     */
    public function getAttributeDescriptions(iterable $voters): AttributeDescriptionsCollection
    {
        $collection = new AttributeDescriptionsCollection();
        foreach ($voters as $voter) {
            foreach ($voter::getAttributes() as $attribute) {
                $collection->add(new AttributeDescription($attribute, $voter instanceof ModuleVoterInterface, $this->getTranslator(), $this->getTranslationDomain()));
            }
        }

        return $collection->sort();
    }

    /**
     * Returns attributes descriptions of module voters
     */
    public function getModuleAttributeDescriptions(string $moduleCode): AttributeDescriptionsCollection
    {
        return $this->getAttributeDescriptions($this->voters->filter(
            static fn (AbstractVoter $voter): bool => $voter instanceof ModuleVoterInterface && $voter::getModule() === $moduleCode,
        ));
    }
}
