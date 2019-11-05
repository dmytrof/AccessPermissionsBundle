<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Tests\Service;

use Dmytrof\AccessPermissionsBundle\Security\AbstractVoter;
use Dmytrof\AccessPermissionsBundle\Security\ModuleVoterInterface;
use Dmytrof\AccessPermissionsBundle\Security\Traits\ModuleVoterTrait;
use Dmytrof\AccessPermissionsBundle\Service\RolesContainer;
use Dmytrof\AccessPermissionsBundle\Service\VotersContainer;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class VotersContainerTest extends TestCase
{
    protected $rolesContainer;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->rolesContainer = new RolesContainer(['ROLE_ADMIN' => ['ROLE_USER']]);
    }

    /**
     * @return VotersContainer
     */
    public function testCreateVotersContainer(): VotersContainer
    {
        $translator = $this->createMock(TranslatorInterface::class);

        $container = new VotersContainer([new VoterA($this->rolesContainer), new VoterB($this->rolesContainer)], $translator);
        $this->assertCount(2, $container);
        $this->assertInstanceOf(\Iterator::class, $container->getIterator());

        return $container;
    }

    /**
     * @depends testCreateVotersContainer
     * @param VotersContainer $container
     * @return VotersContainer
     */
    public function testAddVoters(VotersContainer $container): VotersContainer
    {
        $this->assertInstanceOf(VotersContainer::class, $container->addVoter(new VoterC($this->rolesContainer)));
        $this->assertCount(3, $container);

        return $container;
    }

    /**
     * @depends testAddVoters
     * @param VotersContainer $container
     */
    public function testAttributes(VotersContainer $container): void
    {
        $this->assertCount(6, $container->getAllAttributes());
        $this->assertCount(4, $container->getVotersAttributes([new VoterA($this->rolesContainer), new VoterB($this->rolesContainer)]));

        $this->assertInstanceOf(Collection::class, $container->getAttributeDescriptionsCollection());
        $this->assertCount(6, $container->getAttributeDescriptionsCollection());

        $this->assertInstanceOf(Collection::class, $container->getModuleAttributeDescriptions(VoterC::MODULE));
        $this->assertCount(2, $container->getModuleAttributeDescriptions(VoterC::MODULE));

        $this->assertInstanceOf(Collection::class, $container->getAttributeDescriptions([new VoterA($this->rolesContainer), new VoterC($this->rolesContainer)]));
        $this->assertCount(4, $container->getAttributeDescriptions([new VoterA($this->rolesContainer), new VoterC($this->rolesContainer)]));
    }
}

class VoterA extends AbstractVoter
{
    public const ATTRIBUTES = [
        'test.a.view',
        'test.a.create',
    ];
}

class VoterB extends AbstractVoter
{
    public const ATTRIBUTES = [
        'test.b.view',
        'test.b.create',
    ];
}

class VoterC extends AbstractVoter implements ModuleVoterInterface
{
    use ModuleVoterTrait;

    public const MODULE = 'moduleC';
    public const ATTRIBUTES = [
        'test.module_c.c.view',
        'test.module_c.c.create',
    ];
}