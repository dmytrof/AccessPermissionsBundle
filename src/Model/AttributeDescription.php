<?php

/*
 * This file is part of the DmytrofFractalBundle package.
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
    public const TRANSLATION_DOMAIN = 'module';

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
     * @param TranslatorInterface|null $translator
     */
    public function __construct(string $attribute, TranslatorInterface $translator = null)
    {
        $this->translator = $translator;
        $this->attribute = $attribute;
        $this->attributeComponents = new AttributeComponents($attribute);
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
     * Translates label key
     * @param string $labelKey
     * @return string
     */
    protected function translate(string $labelKey): string
    {
        return $this->translator ? $this->translator->trans($labelKey, [], static::TRANSLATION_DOMAIN) : $labelKey;
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
        return join(' > ', [/*$this->getVendor(), */$this->getModule(), $this->getSubject(), $this->getShortLabel()]);
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
     * @return string
     */
    public function getModule(): string
    {
        return $this->translate($this->getAttributeComponents()->getModuleLabelKey());
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
}