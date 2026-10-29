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

namespace Dmytrof\AccessPermissionsBundle\Tests\DependencyInjection;

use Dmytrof\AccessPermissionsBundle\DependencyInjection\{Configuration, DmytrofAccessPermissionsExtension};
use Dmytrof\AccessPermissionsBundle\DmytrofAccessPermissionsBundle;
use Dmytrof\AccessPermissionsBundle\Form\DataTransformer\AccessAttributeToStringTransformer;
use Dmytrof\AccessPermissionsBundle\Form\Type\{AccessAttributesChoiceType, AccessAttributesCollectionType, AccessAttributeType};
use Dmytrof\AccessPermissionsBundle\Security\AbstractVoter;
use Dmytrof\AccessPermissionsBundle\Service\{RolesContainer, VotersContainer};
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DmytrofAccessPermissionsExtensionTest extends TestCase
{
    private function load(array $config = []): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new DmytrofAccessPermissionsExtension())->load([$config], $container);

        return $container;
    }

    public function testAlias(): void
    {
        $this->assertSame('dmytrof_access_permissions', (new DmytrofAccessPermissionsExtension())->getAlias());
        $this->assertInstanceOf(DmytrofAccessPermissionsExtension::class, (new DmytrofAccessPermissionsBundle())->getContainerExtension());
    }

    public function testConfiguration(): void
    {
        $processor = new Processor();

        $this->assertSame(['translation_domain' => 'access_attributes'], $processor->processConfiguration(new Configuration(), [[]]));
        $this->assertSame(['translation_domain' => 'permissions'], $processor->processConfiguration(new Configuration(), [['translation_domain' => 'permissions']]));
    }

    public function testServices(): void
    {
        $container = $this->load();

        foreach ([AccessAttributeToStringTransformer::class, AccessAttributeType::class, AccessAttributesChoiceType::class, AccessAttributesCollectionType::class] as $id) {
            $this->assertTrue($container->hasDefinition($id), $id);
            $this->assertTrue($container->getDefinition($id)->isAutoconfigured(), $id);
        }

        $votersContainer = $container->getDefinition(VotersContainer::class);
        $this->assertTrue($votersContainer->isPublic());
        $this->assertInstanceOf(TaggedIteratorArgument::class, $votersContainer->getArgument('$voters'));
        $this->assertSame(DmytrofAccessPermissionsExtension::VOTER_TAG, $votersContainer->getArgument('$voters')->getTag());
        $this->assertSame('access_attributes', $votersContainer->getArgument('$translationDomain'));

        $rolesContainer = $container->getDefinition(RolesContainer::class);
        $this->assertTrue($rolesContainer->isPublic());
        $this->assertSame('%security.role_hierarchy.roles%', $rolesContainer->getArgument('$systemRoles'));
    }

    public function testTranslationDomain(): void
    {
        $this->assertSame('permissions', $this->load(['translation_domain' => 'permissions'])->getDefinition(VotersContainer::class)->getArgument('$translationDomain'));
    }

    public function testVotersAutoconfiguration(): void
    {
        $autoconfigured = $this->load()->getAutoconfiguredInstanceof();

        $this->assertArrayHasKey(DmytrofAccessPermissionsExtension::VOTER_TAG, $autoconfigured[AbstractVoter::class]->getTags());
    }
}
