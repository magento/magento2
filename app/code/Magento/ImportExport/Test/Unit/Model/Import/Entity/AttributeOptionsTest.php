<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Import\Entity;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AttributeOptionsTest extends TestCase
{
    /**
     * @param array $options
     * @param array $expected
     * @param array $indexAttributes
     * @return void
     */
    #[DataProvider('optionsProvider')]
    public function testGetAttributeOptions(array $options, array $expected, array $indexAttributes = []): void
    {
        $translations = ['Catalog, Search' => 'Catalogus, zoeken', 'Other' => 'Catalog, Search'];
        $renderer = $this->createMock(RendererInterface::class);
        $renderer->method('render')->willReturnCallback(
            static fn (array $source) => $translations[end($source)] ?? end($source)
        );
        $source = $this->createMock(AbstractSource::class);
        $source->method('getAllOptions')->with(false)->willReturn($options);
        $attribute = $this->createMock(AbstractAttribute::class);
        $attribute->method('usesSource')->willReturn(true);
        $attribute->method('getAttributeCode')->willReturn('visibility');
        $attribute->method('getSource')->willReturn($source);
        $model = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_importData', 'getEntityTypeCode', 'validateRow'])
            ->getMock();
        $initialRenderer = Phrase::getRenderer();
        Phrase::setRenderer($renderer);

        try {
            self::assertEquals($expected, $model->getAttributeOptions($attribute, $indexAttributes));
        } finally {
            Phrase::setRenderer($initialRenderer);
        }
    }

    /**
     * @return array
     */
    public static function optionsProvider(): array
    {
        $visibility = ['value' => '4', 'label' => new Phrase('Catalog, Search')];
        $collision = ['value' => '2', 'label' => new Phrase('Other')];
        $both = ['catalogus, zoeken' => '4', 'catalog, search' => '4'];
        $priority = ['catalogus, zoeken' => '4', 'catalog, search' => '2', 'other' => '2'];

        return [
            'translated and source labels' => [[$visibility], $both],
            'later translated collision' => [[$visibility, $collision], $priority],
            'earlier translated collision' => [[$collision, $visibility], $priority],
            'grouped options' => [[['value' => [$visibility]]], $both],
            'string label' => [[['value' => '4', 'label' => 'Custom label']], ['custom label' => '4']],
            'indexed attribute' => [[$visibility], [4 => '4'], ['visibility']],
            'empty option' => [[['value' => '', 'label' => new Phrase('Select')]], []],
        ];
    }
}
