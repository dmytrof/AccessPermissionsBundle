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

use Dmytrof\AccessPermissionsBundle\Exception\InvalidArgumentException;

/**
 * Parts of attribute "vendor[.module].subject.attribute" and their translation keys
 */
class AttributeComponents
{
    public const ATTRIBUTE_DELIMITER = '.';
    public const ATTRIBUTE_LABEL_DELIMITER = '.';
    public const TRANSLATION_KEY_LABEL = 'label';
    public const TRANSLATION_KEY_DESCRIPTION = 'description';

    protected string $vendor;
    protected bool $useModule;
    protected ?string $module = null;
    protected string $subject;
    protected string $attribute;

    /**
     * @throws InvalidArgumentException when attribute has wrong number of parts
     */
    public function __construct(string $attribute, ?bool $useModule = false)
    {
        $this->useModule = (bool) $useModule;

        $parts = explode(static::ATTRIBUTE_DELIMITER, $attribute);
        $neededPartsCount = $this->isUseModule() ? 4 : 3;
        if (count($parts) !== $neededPartsCount) {
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
     */
    public function isUseModule(): bool
    {
        return $this->useModule;
    }

    /**
     * Generates translation key
     *
     * @param list<string> $parts
     */
    protected function generateTranslationKey(array $parts): string
    {
        return implode(static::ATTRIBUTE_LABEL_DELIMITER, $parts);
    }

    /**
     * Returns translation key parts of vendor and module (if used)
     *
     * @return list<string>
     */
    protected function getVendorAndModuleParts(): array
    {
        return $this->isUseModule() ? [$this->getVendor(), $this->getModule()] : [$this->getVendor()];
    }

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function getVendorLabelKey(): string
    {
        return $this->generateTranslationKey([
            $this->getVendor(),
            static::TRANSLATION_KEY_LABEL,
        ]);
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function getModuleLabelKey(): string
    {
        return $this->generateTranslationKey([
            ...$this->getVendorAndModuleParts(),
            static::TRANSLATION_KEY_LABEL,
        ]);
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getSubjectLabelKey(): string
    {
        return $this->generateTranslationKey([
            ...$this->getVendorAndModuleParts(),
            'subjects',
            $this->getSubject(),
            static::TRANSLATION_KEY_LABEL,
        ]);
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    public function getAttributeLabelKey(): string
    {
        return $this->generateTranslationKey([
            ...$this->getVendorAndModuleParts(),
            'subjects',
            $this->getSubject(),
            'attributes',
            $this->getAttribute(),
            static::TRANSLATION_KEY_LABEL,
        ]);
    }

    /**
     * Returns attribute possible label keys: of the subject, of the module (if used), of the vendor
     *
     * @return list<string>
     */
    public function getAttributeLabelKeys(): array
    {
        $keys = [
            $this->getAttributeLabelKey(),
        ];
        if ($this->isUseModule()) {
            // Attributes in module
            $keys[] = $this->generateTranslationKey([
                $this->getVendor(),
                $this->getModule(),
                'attributes',
                $this->getAttribute(),
                static::TRANSLATION_KEY_LABEL,
            ]);
        }
        // Attributes in vendor
        $keys[] = $this->generateTranslationKey([
            $this->getVendor(),
            'attributes',
            $this->getAttribute(),
            static::TRANSLATION_KEY_LABEL,
        ]);

        return $keys;
    }

    public function getAttributeDescriptionKey(): string
    {
        return $this->generateTranslationKey([
            ...$this->getVendorAndModuleParts(),
            'subjects',
            $this->getSubject(),
            'attributes',
            $this->getAttribute(),
            static::TRANSLATION_KEY_DESCRIPTION,
        ]);
    }
}
