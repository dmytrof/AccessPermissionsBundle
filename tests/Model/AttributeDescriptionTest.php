<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Tests\Model;

use Dmytrof\AccessPermissionsBundle\Model\AttributeComponents;
use Dmytrof\AccessPermissionsBundle\Model\AttributeDescription;
use PHPUnit\Framework\TestCase;

class AttributeDescriptionTest extends TestCase
{
    public function testWithoutModule()
    {
        $parts = ['dmytrof','test','attribute'];
        $attribute = join('.', $parts);
        $attributeDescription = new AttributeDescription($attribute);

        $this->assertSame($attribute, $attributeDescription->getAttribute());
        $this->assertSame($attribute, (string) $attributeDescription);
        $this->assertSame('dmytrof.subjects.test.attributes.attribute.label', $attributeDescription->getShortLabel());

        $this->assertSame('dmytrof.label', $attributeDescription->getVendor());
        $this->assertNull($attributeDescription->getModule());
        $this->assertSame('dmytrof.subjects.test.label', $attributeDescription->getSubject());
        $this->assertSame('dmytrof.subjects.test.label > dmytrof.subjects.test.attributes.attribute.label', $attributeDescription->getLabel());
        $this->assertNull($attributeDescription->getDescription());

        $this->assertEquals([
            'attribute' => $attribute,
            'shortLabel' => 'dmytrof.subjects.test.attributes.attribute.label',
            'label' => 'dmytrof.subjects.test.label > dmytrof.subjects.test.attributes.attribute.label',
            'description' => null,
            'vendor' => 'dmytrof.label',
            'module' => null,
            'subject' => 'dmytrof.subjects.test.label',
        ], $attributeDescription->toArray());
    }

    public function testWithModule()
    {
        $parts = ['dmytrof','article','test','attribute'];
        $attribute = join('.', $parts);
        $attributeDescription = new AttributeDescription($attribute, true);

        $this->assertSame($attribute, $attributeDescription->getAttribute());
        $this->assertSame($attribute, (string) $attributeDescription);
        $this->assertSame('dmytrof.article.subjects.test.attributes.attribute.label', $attributeDescription->getShortLabel());

        $this->assertSame('dmytrof.label', $attributeDescription->getVendor());
        $this->assertSame('dmytrof.article.label', $attributeDescription->getModule());
        $this->assertSame('dmytrof.article.subjects.test.label', $attributeDescription->getSubject());
        $this->assertSame('dmytrof.article.label > dmytrof.article.subjects.test.label > dmytrof.article.subjects.test.attributes.attribute.label', $attributeDescription->getLabel());
        $this->assertNull($attributeDescription->getDescription());

        $this->assertEquals([
            'attribute' => $attribute,
            'shortLabel' => 'dmytrof.article.subjects.test.attributes.attribute.label',
            'label' => 'dmytrof.article.label > dmytrof.article.subjects.test.label > dmytrof.article.subjects.test.attributes.attribute.label',
            'description' => null,
            'vendor' => 'dmytrof.label',
            'module' => 'dmytrof.article.label',
            'subject' => 'dmytrof.article.subjects.test.label',
        ], $attributeDescription->toArray());
    }
}