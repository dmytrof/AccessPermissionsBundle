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

use Dmytrof\AccessPermissionsBundle\{Security\AbstractVoter,
    Security\CRUDVoterInterface,
    Security\Traits\CRUDVoterTrait,
    Service\RolesContainer};
use PHPUnit\Framework\TestCase;

class CRUDVoterTest extends TestCase
{
    public function testVoter()
    {
        $voter = new TestCRUDVoter(new RolesContainer(['ROLE_ADMIN' => []]));

        $this->assertEquals(TestCRUDVoter::VIEW, $voter->getViewAttribute());
        $this->assertEquals(TestCRUDVoter::CREATE, $voter->getCreateAttribute());
        $this->assertEquals(TestCRUDVoter::EDIT, $voter->getEditAttribute());
        $this->assertEquals(TestCRUDVoter::DELETE, $voter->getDeleteAttribute());
    }
}

class TestCRUDVoter extends AbstractVoter implements CRUDVoterInterface
{
    use CRUDVoterTrait;

    public const VIEW = 'view';
    public const CREATE = 'create';
    public const EDIT = 'edit';
    public const DELETE = 'delete';
}