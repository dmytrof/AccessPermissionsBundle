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

namespace Dmytrof\AccessPermissionsBundle\Tests\Model;

use Dmytrof\AccessPermissionsBundle\Model\AttributeDescription;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class AttributeDescriptionTranslationTest extends TestCase
{
    private function translator(array $translations, string $expectedDomain = 'access_attributes'): TranslatorInterface
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(function (string $id, array $parameters, ?string $domain) use ($translations, $expectedDomain): string {
            $this->assertSame($expectedDomain, $domain);

            return $translations[$id] ?? $id;
        });

        return $translator;
    }

    public function testTranslatedLabels(): void
    {
        $description = new AttributeDescription('app.article.view', false, $this->translator([
            'app.label' => 'Application',
            'app.subjects.article.label' => 'Articles',
            'app.subjects.article.attributes.view.label' => 'View articles',
            'app.subjects.article.attributes.view.description' => 'Access to view articles',
        ]), 'access_attributes');

        $this->assertSame([
            'attribute' => 'app.article.view',
            'shortLabel' => 'View articles',
            'label' => 'Articles > View articles',
            'description' => 'Access to view articles',
            'vendor' => 'Application',
            'module' => null,
            'subject' => 'Articles',
        ], $description->toArray());
    }

    public function testShortLabelFallsBackToModuleAndVendorAttributes(): void
    {
        $translations = [
            'app.reports.label' => 'Reports',
            'app.reports.subjects.sales.label' => 'Sales',
            'app.reports.attributes.export.label' => 'Export (module)',
            'app.attributes.export.label' => 'Export (vendor)',
            'app.attributes.view.label' => 'View (vendor)',
        ];

        $export = new AttributeDescription('app.reports.sales.export', true, $this->translator($translations), 'access_attributes');
        $this->assertSame('Export (module)', $export->getShortLabel());
        $this->assertSame('Reports > Sales > Export (module)', $export->getLabel());
        $this->assertNull($export->getDescription());

        $view = new AttributeDescription('app.reports.sales.view', true, $this->translator($translations), 'access_attributes');
        $this->assertSame('View (vendor)', $view->getShortLabel());
    }

    public function testInvalidAttribute(): void
    {
        $this->expectException(\Dmytrof\AccessPermissionsBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Attribute must consist of 3 parts delimited with ".". Input was: app.view');

        new AttributeDescription('app.view');
    }
}
