<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Tests\Form\Type;

use Dmytrof\AccessPermissionsBundle\Entity\AccessAttribute\{AccessAttribute, Repository};
use Dmytrof\AccessPermissionsBundle\Form\{DataTransformer\AccessAttributeToStringTransformer,
    Type\AccessAttributesCollectionType,
    Type\AccessAttributeType};
use Dmytrof\AccessPermissionsBundle\Service\VotersContainer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\{PreloadedExtension, Test\TypeTestCase};

class AccessAttributesCollectionTypeTest extends TypeTestCase
{
    protected const ATTRIBUTE_FOO = 'test.attr.foo';
    protected const ATTRIBUTE_BAR = 'test.attr.bar';

    protected function getExtensions(): array
    {
        $votersContainer = $this->createMock(VotersContainer::class);
        $votersContainer->method('getAllAttributes')->willReturn([
            self::ATTRIBUTE_FOO,
            self::ATTRIBUTE_BAR,
        ]);
        $repo = $this->createMock(Repository::class);
        $repo->method('findItemByAttribute')->willReturn(null);
        $registry = $this->createMock(RegistryInterface::class);
        $registry->method('getRepository')->willReturn($repo);
        $transformer = new AccessAttributeToStringTransformer($registry, $votersContainer);
        // create a type instance with the mocked dependencies
        $type = new AccessAttributeType($transformer);

        return [
            // register the type instances with the PreloadedExtension
            new PreloadedExtension([$type], []),
        ];
    }

    /**
     * @dataProvider getFormData
     * @param array|null $formData
     * @param array $viewData
     * @param array $modelData
     */
    public function testSubmitValidData(?array $formData, array $viewData, array $modelData)
    {
        $form = $this->factory->create(AccessAttributesCollectionType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($modelData, $form->getData());

        $view = $form->createView();
        $this->assertEquals($viewData, $view->vars['value']);
    }

    public function testSubmitInvalidData(): void
    {
        $form = $this->factory->create(AccessAttributesCollectionType::class);

        $form->submit(['foo.bar.wrong', 'bar.foo.wrong']);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals([], $form->getData());
        $view = $form->createView();
        $this->assertEquals([], $view->vars['value']);
    }

    /**
     * @return array
     */
    public function getFormData(): array
    {
        return [
            [[self::ATTRIBUTE_FOO, self::ATTRIBUTE_BAR], [self::ATTRIBUTE_FOO, self::ATTRIBUTE_BAR], [new AccessAttribute(self::ATTRIBUTE_FOO), new AccessAttribute(self::ATTRIBUTE_BAR)]],
            [[self::ATTRIBUTE_BAR, 'test.value'], [self::ATTRIBUTE_BAR], [new AccessAttribute(self::ATTRIBUTE_BAR)]],
            [null, [], []],
        ];
    }
}