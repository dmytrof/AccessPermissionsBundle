<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Tests\Form\DataTransformer;

use Dmytrof\AccessPermissionsBundle\Entity\AccessAttribute\{AccessAttribute, Repository};
use Dmytrof\AccessPermissionsBundle\Exception\InvalidArgumentException;
use Dmytrof\AccessPermissionsBundle\Model\AccessAttribute as AccessAttributeModel;
use Dmytrof\AccessPermissionsBundle\Form\DataTransformer\AccessAttributeToStringTransformer;
use Dmytrof\AccessPermissionsBundle\Security\AbstractVoter;
use Dmytrof\AccessPermissionsBundle\Service\{RolesContainer, VotersContainer};
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccessAttributeToStringTransformerTest extends TestCase
{
    /**
     * @return AccessAttributeToStringTransformer
     */
    public function testTransformer(): AccessAttributeToStringTransformer
    {
        $rolesContainer = new RolesContainer(['ROLE_ADMIN' => []]);
        $repo = $this->createMock(Repository::class);
        $repo->method('findItemByAttribute')->willReturn(null);
        $registry = $this->createMock(RegistryInterface::class);
        $registry->method('getRepository')->willReturn($repo);
        $translator = $this->createMock(TranslatorInterface::class);
        $transformer = new AccessAttributeToStringTransformer($registry, new VotersContainer([new TestVoterA($rolesContainer), new TestVoterB($rolesContainer)], $translator));

        $this->assertCount(4, $transformer->getAttributes());
        $this->assertEquals(AccessAttribute::class, $transformer->getEntityClass());
        $this->assertEquals(TestAccessAttribute::class, $transformer->setEntityClass(TestAccessAttribute::class)->getEntityClass());

        return $transformer;
    }

    /**
     * @depends testTransformer
     * @param AccessAttributeToStringTransformer $transformer
     */
    public function testTransform(AccessAttributeToStringTransformer $transformer): void
    {
        $this->assertEquals('test.a.view', $transformer->transform(new TestAccessAttribute('test.a.view')));
        $this->assertNull($transformer->transform(\stdClass::class));
        $this->assertNull($transformer->transform('some_value'));
        $this->assertNull($transformer->transform(null));

        $this->expectException(InvalidArgumentException::class);
        $transformer->setEntityClass(\stdClass::class);
    }

    /**
     * @depends testTransformer
     * @param AccessAttributeToStringTransformer $transformer
     */
    public function testReverseTransform(AccessAttributeToStringTransformer $transformer): void
    {
        $this->assertInstanceOf(TestAccessAttribute::class, $transformer->reverseTransform('test.a.view'));
        $this->assertNull($transformer->reverseTransform(null));
        $this->assertNull($transformer->reverseTransform(''));
        $this->expectException(TransformationFailedException::class);
        $this->assertNull($transformer->reverseTransform('test.www.view'));
    }
}

class TestAccessAttribute extends AccessAttributeModel
{
}

class TestVoterA extends AbstractVoter
{
    public const ATTRIBUTES = [
        'test.a.view',
        'test.a.create',
    ];
}

class TestVoterB extends AbstractVoter
{
    public const ATTRIBUTES = [
        'test.b.view',
        'test.b.create',
    ];
}