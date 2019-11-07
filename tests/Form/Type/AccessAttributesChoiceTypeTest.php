<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Tests\Form\Type;

use Dmytrof\AccessPermissionsBundle\Entity\AccessAttribute\{AccessAttribute, Repository};
use Dmytrof\AccessPermissionsBundle\Form\{DataTransformer\AccessAttributeToStringTransformer, Type\AccessAttributesChoiceType};
use Dmytrof\AccessPermissionsBundle\Service\VotersContainer;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\{PreloadedExtension, Test\TypeTestCase};

class AccessAttributesChoiceTypeTest extends TypeTestCase
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
        $type = new AccessAttributesChoiceType($transformer);

        return [
            // register the type instances with the PreloadedExtension
            new PreloadedExtension([$type], []),
        ];
    }

    /**
     * @dataProvider getFormData
     * @param array|null $formData
     * @param bool $synchronized
     * @param $viewData
     * @param $modelData
     */
    public function testSubmitValidData(?array $formData, bool $synchronized, $viewData, $modelData)
    {
        $form = $this->factory->create(AccessAttributesChoiceType::class);
        $form->submit($formData);

        $this->assertSame($synchronized, $form->isSynchronized());

        $this->assertEquals($modelData, $form->getData());

        $view = $form->createView();
        $this->assertEquals($viewData, $view->vars['value']);
    }

    public function testSubmitInvalidData(): void
    {
        $form = $this->factory->create(AccessAttributesChoiceType::class);

        $wrongData = ['foo.bar.wrong', 'bar.foo.wrong'];
        $form->submit($wrongData);

        $this->assertFalse($form->isSynchronized());

        $this->assertNull($form->getData());
        $view = $form->createView();
        $this->assertEquals($wrongData, $view->vars['value']);
    }

    /**
     * @return array
     */
    public function getFormData(): array
    {
        return [
            [[self::ATTRIBUTE_FOO, self::ATTRIBUTE_BAR], true, new ArrayCollection([self::ATTRIBUTE_FOO, self::ATTRIBUTE_BAR]), new ArrayCollection([new AccessAttribute(self::ATTRIBUTE_FOO), new AccessAttribute(self::ATTRIBUTE_BAR)])],
            [[self::ATTRIBUTE_BAR, 'foo.bar'], false, [self::ATTRIBUTE_BAR, 'foo.bar'], null],
            [null, true, new ArrayCollection(), new ArrayCollection()],
        ];
    }
}