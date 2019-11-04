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

use Dmytrof\AccessPermissionsBundle\Exception\InvalidArgumentException;

class AttributeComponents
{
    public const ATTRIBUTE_DELIMITER = '.';
    public const ATTRIBUTE_LABEL_DELIMITER = '.';
    public const TRANSLATION_KEY_LABEL = 'label';
    public const TRANSLATION_KEY_DESCRIPTION = 'description';

    /**
     * @var string
     */
    protected $vendor;

    /**
     * @var bool
     */
    protected $useModule;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $attribute;

    /**
     * AttributeComponents constructor.
     * @param string $attribute
     * @param bool $useModule
     */
    public function __construct(string $attribute, bool $useModule = false)
    {
        $this->useModule = $useModule;

        $parts = explode(static::ATTRIBUTE_DELIMITER, $attribute);
        $neededPartsCount = $this->isUseModule() ? 4 : 3;
        if (sizeof($parts) != $neededPartsCount) {
            throw new InvalidArgumentException(sprintf('Attribute must consist of %s parts delimited with ".". Input was: %s', $neededPartsCount, $attribute));
        }
        $this->vendor = array_shift($parts);
        if ($this->isUseModule()) {
            $this->module = array_shift($parts);
        }
        $this->subject = array_shift($parts);
        $this->attribute = array_shift($parts);
    }

    /**
     * Checks if module is used in attribute
     * @return bool
     */
    public function isUseModule(): bool
    {
        return $this->useModule;
    }

    /**
     * Generates translation key
     * @param array $parts
     * @return string
     */
    protected function generateTranslationKey(array $parts): string
    {
        return join(static::ATTRIBUTE_LABEL_DELIMITER, $parts);
    }

    /**
     * Returns vendor
     * @return string
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * Returns vendor label key
     * @return string
     */
    public function getVendorLabelKey(): string
    {
        return $this->generateTranslationKey([
            $this->getVendor(),
            static::TRANSLATION_KEY_LABEL,
        ]);
    }

    /**
     * Returns module
     * @return string|null
     */
    public function getModule(): ?string
    {
        return $this->module;
    }

    /**
     * Returns module label key
     * @return string
     */
    public function getModuleLabelKey(): string
    {
        $parts = [
            $this->getVendor(),
        ];
        if ($this->isUseModule()) {
            array_push($parts, $this->getModule());
        }
        array_push($parts, static::TRANSLATION_KEY_LABEL);
        return $this->generateTranslationKey($parts);
    }

    /**
     * Returns subject
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Returns subject label key
     * @return string
     */
    public function getSubjectLabelKey(): string
    {
        $parts = [
            $this->getVendor(),
        ];
        if ($this->isUseModule()) {
            array_push($parts, $this->getModule());
        }
        array_push($parts, ...[
            'subjects',
            $this->getSubject(),
            static::TRANSLATION_KEY_LABEL,
        ]);
        return $this->generateTranslationKey($parts);
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
     * Returns attribute label key
     * @return string
     */
    public function getAttributeLabelKey(): string
    {
        $parts = [
            $this->getVendor(),
        ];
        if ($this->isUseModule()) {
            array_push($parts, $this->getModule());
        }
        array_push($parts, ...[
            'subjects',
            $this->getSubject(),
            'attributes',
            $this->getAttribute(),
            static::TRANSLATION_KEY_LABEL,
        ]);
        return $this->generateTranslationKey($parts);
    }

    /**
     * Returns attribute possible label keys
     * @return array
     */
    public function getAttributeLabelKeys(): array
    {
        $keys = [
            $this->getAttributeLabelKey(),
        ];
        if ($this->isUseModule()) {
            // Attributes in module
            array_push($keys, $this->generateTranslationKey([
                $this->getVendor(),
                $this->getModule(),
                'attributes',
                $this->getAttribute(),
                static::TRANSLATION_KEY_LABEL,
            ]));
        }
        // Attributes in vendor
        array_push($keys, $this->generateTranslationKey([
            $this->getVendor(),
            'attributes',
            $this->getAttribute(),
            static::TRANSLATION_KEY_LABEL,
        ]));

        return $keys;
    }

    /**
     * Returns attribute description key
     * @return string
     */
    public function getAttributeDescriptionKey(): string
    {
        $parts = [
            $this->getVendor(),
        ];
        if ($this->isUseModule()) {
            array_push($parts, $this->getModule());
        }
        array_push($parts, ...[
            'subjects',
            $this->getSubject(),
            'attributes',
            $this->getAttribute(),
            static::TRANSLATION_KEY_DESCRIPTION,
        ]);
        return $this->generateTranslationKey($parts);
    }
}