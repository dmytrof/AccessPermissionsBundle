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
use PHPUnit\Framework\TestCase;

class AttributeComponentsTest extends TestCase
{
    public function testWithoutModule()
    {
        $parts = ['dmytrof','test','attribute'];
        $attribute = join('.', $parts);
        $attributeComponents = new AttributeComponents($attribute);

        $this->assertFalse($attributeComponents->isUseModule());
        $this->assertSame($parts[0], $attributeComponents->getVendor());
        $this->assertSame($parts[1], $attributeComponents->getSubject());
        $this->assertSame($parts[2], $attributeComponents->getAttribute());
        $this->assertNull($attributeComponents->getModule());

        $this->assertSame('dmytrof.label', $attributeComponents->getVendorLabelKey());
        $this->assertSame('dmytrof.label', $attributeComponents->getModuleLabelKey());
        $this->assertSame('dmytrof.subjects.test.label', $attributeComponents->getSubjectLabelKey());
        $this->assertSame('dmytrof.subjects.test.attributes.attribute.label', $attributeComponents->getAttributeLabelKey());
        $this->assertSame('dmytrof.subjects.test.attributes.attribute.description', $attributeComponents->getAttributeDescriptionKey());

        $this->assertEquals([
            'dmytrof.subjects.test.attributes.attribute.label',
            'dmytrof.attributes.attribute.label',
        ], $attributeComponents->getAttributeLabelKeys());
    }

    public function testWithModule()
    {
        $parts = ['dmytrof','article','test','attribute'];
        $attribute = join('.', $parts);
        $attributeComponents = new AttributeComponents($attribute, true);

        $this->assertTrue($attributeComponents->isUseModule());
        $this->assertSame($parts[0], $attributeComponents->getVendor());
        $this->assertSame($parts[1], $attributeComponents->getModule());
        $this->assertSame($parts[2], $attributeComponents->getSubject());
        $this->assertSame($parts[3], $attributeComponents->getAttribute());

        $this->assertSame('dmytrof.label', $attributeComponents->getVendorLabelKey());
        $this->assertSame('dmytrof.article.label', $attributeComponents->getModuleLabelKey());
        $this->assertSame('dmytrof.article.subjects.test.label', $attributeComponents->getSubjectLabelKey());
        $this->assertSame('dmytrof.article.subjects.test.attributes.attribute.label', $attributeComponents->getAttributeLabelKey());
        $this->assertSame('dmytrof.article.subjects.test.attributes.attribute.description', $attributeComponents->getAttributeDescriptionKey());

        $this->assertEquals([
            'dmytrof.article.subjects.test.attributes.attribute.label',
            'dmytrof.article.attributes.attribute.label',
            'dmytrof.attributes.attribute.label',
        ], $attributeComponents->getAttributeLabelKeys());
    }
}