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

use Dmytrof\AccessPermissionsBundle\{Security\ModuleVoter, Service\RolesContainer};
use PHPUnit\Framework\TestCase;

class ModuleVoterTest extends TestCase
{
    public function testVoter()
    {
        $voter = new TestModuleVoter(new RolesContainer(['ROLE_ADMIN' => []]));

        $this->assertEquals(TestModuleVoter::MODULE, $voter->getModule());
    }
}

class TestModuleVoter extends ModuleVoter
{
    public const MODULE = 'some_module';
}