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

namespace Dmytrof\AccessPermissionsBundle\Model;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Translated labels and description of access attribute
 */
class AttributeDescription implements \Stringable
{
    protected AttributeComponents $attributeComponents;

    public function __construct(
        protected string $attribute,
        bool $useModule = false,
        protected ?TranslatorInterface $translator = null,
        protected ?string $translationDomain = null,
    ) {
        $this->attributeComponents = new AttributeComponents($attribute, $useModule);
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    protected function getAttributeComponents(): AttributeComponents
    {
        return $this->attributeComponents;
    }

    protected function isUseModule(): bool
    {
        return $this->getAttributeComponents()->isUseModule();
    }

    /**
     * Translates label key (returns the key without translator)
     */
    protected function translate(string $labelKey): string
    {
        return $this->translator?->trans($labelKey, [], $this->translationDomain) ?? $labelKey;
    }

    /**
     * Translates label keys
     *
     * @param array<array-key, string> $labelKeys
     *
     * @return array<array-key, string>
     */
    protected function translateList(array $labelKeys): array
    {
        return array_map($this->translate(...), $labelKeys);
    }

    /**
     * Returns the first translated label of the attribute (or the first label key)
     */
    public function getShortLabel(): string
    {
        $keys = $this->getAttributeComponents()->getAttributeLabelKeys();
        $translations = $this->translateList($keys);
        foreach ($translations as $key => $value) {
            if ($value !== $keys[$key]) {
                return $value;
            }
        }

        return reset($translations);
    }

    /**
     * Returns label: "[Module > ]Subject > Attribute"
     */
    public function getLabel(): string
    {
        $parts = [$this->getSubject(), $this->getShortLabel()];
        if ($this->isUseModule()) {
            array_unshift($parts, $this->getModule());
        }

        return implode(' > ', $parts);
    }

    public function getDescription(): ?string
    {
        $descriptionKey = $this->getAttributeComponents()->getAttributeDescriptionKey();
        $description = $this->translate($descriptionKey);

        return $description !== $descriptionKey ? $description : null;
    }

    public function getVendor(): string
    {
        return $this->translate($this->getAttributeComponents()->getVendorLabelKey());
    }

    public function getModule(): ?string
    {
        return $this->isUseModule() ? $this->translate($this->getAttributeComponents()->getModuleLabelKey()) : null;
    }

    public function getSubject(): string
    {
        return $this->translate($this->getAttributeComponents()->getSubjectLabelKey());
    }

    public function __toString(): string
    {
        return $this->getAttribute();
    }

    /**
     * @return array{attribute: string, shortLabel: string, label: string, description: ?string, vendor: string, module: ?string, subject: string}
     */
    public function toArray(): array
    {
        return [
            'attribute' => $this->getAttribute(),
            'shortLabel' => $this->getShortLabel(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
            'vendor' => $this->getVendor(),
            'module' => $this->getModule(),
            'subject' => $this->getSubject(),
        ];
    }
}
