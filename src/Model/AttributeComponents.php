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
     */
    public function __construct(string $attribute)
    {
        $parts = explode(static::ATTRIBUTE_DELIMITER, $attribute);
        if (sizeof($parts) != 4) {
            throw new InvalidArgumentException(sprintf('Attribute must consist of 4 parts delimited with ".". Input was: %s', $attribute));
        }
        $this->vendor = array_shift($parts);
        $this->module = array_shift($parts);
        $this->subject = array_shift($parts);
        $this->attribute = array_shift($parts);
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
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * Returns module label key
     * @return string
     */
    public function getModuleLabelKey(): string
    {
        return $this->generateTranslationKey([
            $this->getVendor(),
            $this->getModule(),
            static::TRANSLATION_KEY_LABEL,
        ]);
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
        return $this->generateTranslationKey([
            $this->getVendor(),
            $this->getModule(),
            'subjects',
            $this->getSubject(),
            static::TRANSLATION_KEY_LABEL,
        ]);
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
        return $this->generateTranslationKey([
            $this->getVendor(),
            $this->getModule(),
            'subjects',
            $this->getSubject(),
            'attributes',
            $this->getAttribute(),
            static::TRANSLATION_KEY_LABEL,
        ]);
    }

    /**
     * Returns attribute possible label keys
     * @return array
     */
    public function getAttributeLabelKeys(): array
    {
        return [
            $this->getAttributeLabelKey(),
            // Attributes in module
            $this->generateTranslationKey([
                $this->getVendor(),
                $this->getModule(),
                'attributes',
                $this->getAttribute(),
                static::TRANSLATION_KEY_LABEL,
            ]),
            // Attributes in vendor
            $this->generateTranslationKey([
                $this->getVendor(),
                'attributes',
                $this->getAttribute(),
                static::TRANSLATION_KEY_LABEL,
            ]),
        ];
    }

    /**
     * Returns attribute description key
     * @return string
     */
    public function getAttributeDescriptionKey(): string
    {
        return $this->generateTranslationKey([
            $this->getVendor(),
            $this->getModule(),
            'subjects',
            $this->getSubject(),
            'attributes',
            $this->getAttribute(),
            static::TRANSLATION_KEY_DESCRIPTION,
        ]);
    }
}