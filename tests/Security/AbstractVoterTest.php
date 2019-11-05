<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Tests\Security;

use Dmytrof\AccessPermissionsBundle\{Model\AccessAttribute, Security\AbstractVoter, Service\RolesContainer};
use PHPUnit\Framework\TestCase;

class AbstractVoterTest extends TestCase
{
    public function testVoter()
    {
        $voter = new TestVoter(new RolesContainer(['ROLE_ADMIN' => []]));

        $this->assertInstanceOf(RolesContainer::class, $voter->getRolesContainer());
        $this->assertEquals(TestVoter::ATTRIBUTES, $voter->getAttributes());

        $this->assertTrue($voter->checkAttribute(TestVoter::VIEW));
        $this->assertFalse($voter->checkAttribute('foo'));

        $this->assertSame(TestVoter::PREFIX, $voter->getPrefix());
        $this->assertEquals([AccessAttribute::class], $voter->getSubject());

        $this->assertEquals('view', $voter->getShortAttribute(TestVoter::VIEW));
    }
}

class TestVoter extends AbstractVoter
{
    protected const SUBJECT = [AccessAttribute::class];

    public const PREFIX = 'test.';
    public const VIEW = self::PREFIX.'view';
    public const EDIT = self::PREFIX.'edit';

    public const ATTRIBUTES = [
        self::VIEW,
        self::EDIT,
    ];
}