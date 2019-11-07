<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Model;

use Symfony\Contracts\Translation\TranslatorInterface;

class AttributeDescription
{
    /**
     * @var string
     */
    protected $translationDomain;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $attribute;

    /**
     * @var AttributeComponents
     */
    protected $attributeComponents;

    /**
     * AttributeDescription constructor.
     * @param string $attribute
     * @param bool $useModule
     * @param TranslatorInterface|null $translator
     * @param string|null $translationDomain
     */
    public function __construct(string $attribute, bool $useModule = false, TranslatorInterface $translator = null, ?string $translationDomain = null)
    {
        $this->attribute = $attribute;
        $this->attributeComponents = new AttributeComponents($attribute, $useModule);
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
    }

    /**
     * Returns attribute
     * @return string
     */
    public function getAttribute(): string
    {
        return $this->attribute;
    }

    /**
     * Returns attribute components
     * @return AttributeComponents
     */
    protected function getAttributeComponents(): AttributeComponents
    {
        if (is_null($this->attributeComponents)) {
            $this->attributeComponents = new AttributeComponents($this->getAttribute());
        }
        return $this->attributeComponents;
    }

    /**
     * Checks if module is used
     * @return bool
     */
    protected function isUseModule(): bool
    {
        return $this->getAttributeComponents()->isUseModule();
    }

    /**
     * Translates label key
     * @param string $labelKey
     * @return string
     */
    protected function translate(string $labelKey): string
    {
        return $this->translator ? $this->translator->trans($labelKey, [], $this->translationDomain) : $labelKey;
    }

    /**
     * Translates label keys
     * @param array $labelKeys
     * @return array
     */
    protected function translateList(array $labelKeys): array
    {
        if ($this->translator) {
            foreach ($labelKeys as $key => $title) {
                $labelKeys[$key] = $this->translate($title);
            }
        }
        return $labelKeys;
    }

    /**
     * Returns label
     * @return string
     */
    public function getShortLabel(): string
    {
        $keys = $this->getAttributeComponents()->getAttributeLabelKeys();
        $translations = $this->translateList($keys);
        foreach ($translations as $key => $value) {
            if ($value != $keys[$key]) {
                return $value;
            }
        }
        return array_shift($translations);
    }

    /**
     * Returns label
     * @return string
     */
    public function getLabel(): string
    {
        $parts = [$this->getSubject(), $this->getShortLabel()];
        if ($this->isUseModule()) {
            array_unshift($parts, $this->getModule());
        }
        return join(' > ', $parts);
    }

    /**
     * Returns description
     * @return null|string
     */
    public function getDescription(): ?string
    {
        $descriptionKey = $this->getAttributeComponents()->getAttributeDescriptionKey();
        $description = $this->translate($descriptionKey);
        return $description != $descriptionKey ? $description : null;
    }

    /**
     * Returns vendor
     * @return string
     */
    public function getVendor(): string
    {
        return $this->translate($this->getAttributeComponents()->getVendorLabelKey());
    }

    /**
     * Returns module
     * @return string|null
     */
    public function getModule(): ?string
    {
        return $this->isUseModule() ? $this->translate($this->getAttributeComponents()->getModuleLabelKey()) : null;
    }

    /**
     * Returns subject
     * @return string
     */
    public function getSubject(): string
    {
        return $this->translate($this->getAttributeComponents()->getSubjectLabelKey());
    }

    public function __toString()
    {
        return $this->getAttribute();
    }

    /**
     * Transforms attributeDescription to array
     * @return array
     */
    public function toArray(): array
    {
        return [
            'attribute' => $this->getAttribute(),
            'shortLabel' => $this->getShortLabel(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
            'vendor' => $this->getVendor(),
            'module' => $this->isUseModule() ? $this->getModule() : null,
            'subject' => $this->getSubject(),
        ];
    }
}