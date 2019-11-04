<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Tests\Model;

use Dmytrof\AccessPermissionsBundle\Model\AccessAttribute;
use PHPUnit\Framework\TestCase;

class AccessAttributeTest extends TestCase
{
    public function testAccessAttribute()
    {
        $attribute = 'dmytrof.test.attribute';
        $accessAttribute = new AccessAttribute();

        $this->assertNull($accessAttribute->getAttribute());
        $this->assertNull($accessAttribute->getId());
        $this->assertSame($attribute, $accessAttribute->setAttribute($attribute)->getAttribute());
        $this->assertSame(22, $accessAttribute->setId(22)->getId());

        $this->assertSame($attribute, (new AccessAttribute($attribute))->getAttribute());
    }
}