<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Tests\Service;

use Dmytrof\AccessPermissionsBundle\Service\RolesContainer;
use PHPUnit\Framework\TestCase;

class RolesContainerTest extends TestCase
{
    /**
     * @return RolesContainer
     */
    public function testCreateRolesContainer(): RolesContainer
    {
        $container = new RolesContainer([]);
        $this->assertCount(0, $container);

        $container = new RolesContainer(['ROLE_ADMIN' => ['ROLE_USER'], 'ROLE_SUPER_ADMIN' => ['ROLE_ADMIN', 'ROLE_OTHER']]);
        $this->assertCount(4, $container);
        $this->assertCount(4, $container->getRoles());
        $this->assertInstanceOf(\Iterator::class, $container->getIterator());

        return $container;
    }

    /**
     * @depends testCreateRolesContainer
     * @param RolesContainer $container
     * @return RolesContainer
     */
    public function testAddAndRemoveRoles(RolesContainer $container): RolesContainer
    {
        $this->assertInstanceOf(RolesContainer::class, $container->addRole('ROLE_TEST', 'ROLE_TEST2'));
        $this->assertCount(6, $container);

        $this->assertInstanceOf(RolesContainer::class, $container->removeRole('ROLE_TEST', 'ROLE_TEST2', 'ROLE_OTHER'));
        $this->assertCount(3, $container);

        return $container;
    }

    public function testClassify(): void
    {
        $container = new RolesContainer(['ROLE_SUPER_ADMIN' => ['ROLE_ADMIN']]);
        $this->assertEquals('RoleAdmin', $container->classifyRole('ROLE_ADMIN'));
        $this->assertEquals('ROLE_ADMIN', $container->declassifyRole($container->classifyRole('ROLE_ADMIN')));
        $this->assertEquals('RoleSuperAdmin|RoleAdmin', $container->getRolesPattern());
    }
}