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

use Dmytrof\AccessPermissionsBundle\Exception\InvalidArgumentException;
use Dmytrof\AccessPermissionsBundle\Model\{AttributeDescription, AttributeDescriptionsCollection};
use PHPUnit\Framework\TestCase;

class AttributeDescriptionsCollectionTest extends TestCase
{
    /**
     * @return AttributeDescriptionsCollection
     */
    public function testCreate(): AttributeDescriptionsCollection
    {
        $collection = new AttributeDescriptionsCollection();
        $this->assertCount(0, $collection);

        $collection = new AttributeDescriptionsCollection([new AttributeDescription('test.a.view'), new AttributeDescription('test.module_a.a.view', true)]);
        $this->assertCount(2, $collection);

        return $collection;
    }

    /**
     * @depends testCreate
     * @param AttributeDescriptionsCollection $collection
     */
    public function testGetAsArray(AttributeDescriptionsCollection $collection): void
    {
        $this->assertIsArray($collection->getAsArray());
        $this->assertCount(2, $collection->getAsArray());
    }

    /**
     * @depends testCreate
     * @param AttributeDescriptionsCollection $collection
     * @return AttributeDescriptionsCollection
     */
    public function testSetAdd(AttributeDescriptionsCollection $collection): void
    {
        $this->assertTrue($collection->add(new AttributeDescription('test.b.create')));
        $this->assertCount(3, $collection);

        $this->assertEmpty($collection->set('test', new AttributeDescription('test.b.delete')));
        $this->assertCount(4, $collection);

        $this->expectException(InvalidArgumentException::class);
        $collection->add('test.b.create');
    }
}