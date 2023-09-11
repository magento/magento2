<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Config;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\CatalogGraphQl\Model\Config\FilterAttributeReader;
use Magento\Framework\GraphQl\Schema\Type\Entity\MapperInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterAttributeReaderTest extends TestCase
{
    /**
     * @var MapperInterface|MockObject
     */
    private $mapperMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var FilterAttributeReader
     */
    private $model;

    protected function setUp(): void
    {
        $this->mapperMock = $this->createMock(MapperInterface::class);
        $this->collectionFactoryMock = $this->createMock(AttributeCollectionFactory::class);
        $this->model = new FilterAttributeReader($this->mapperMock, $this->collectionFactoryMock);
    }

    /**
     * @dataProvider readDataProvider
     * @param string $filterableAttrCode
     * @param string $filterableAttrInput
     * @param string $searchableAttrCode
     * @param string $searchableAttrInput
     * @param array $fieldsType
     */
    public function testRead(
        string $filterableAttrCode,
        string $filterableAttrInput,
        string $searchableAttrCode,
        string $searchableAttrInput,
        array $fieldsType
    ): void {
        $this->mapperMock->expects(self::once())
            ->method('getMappedTypes')
            ->with('filter_attributes')
            ->willReturn(['product_filter_attributes' => 'ProductAttributeFilterInput']);

        $filterableAttributeCollection = $this->createMock(AttributeCollection::class);
        $filterableAttributeCollection->expects(self::once())
            ->method('addHasOptionsFilter')
            ->willReturnSelf();
        $filterableAttributeCollection->expects(self::once())
            ->method('addIsFilterableFilter')
            ->willReturnSelf();
        $filterableAttribute = $this->createMock(Attribute::class);
        $filterableAttributeCollection->expects(self::once())
            ->method('getItems')
            ->willReturn(array_filter([11 => $filterableAttribute]));
        $searchableAttributeCollection = $this->createMock(AttributeCollection::class);
        $searchableAttributeCollection->expects(self::once())
            ->method('addHasOptionsFilter')
            ->willReturnSelf();
        $searchableAttributeCollection->expects(self::once())
            ->method('addIsSearchableFilter')
            ->willReturnSelf();
        $searchableAttributeCollection->expects(self::once())
            ->method('addDisplayInAdvancedSearchFilter')
            ->willReturnSelf();
        $searchableAttribute = $this->createMock(Attribute::class);
        $searchableAttributeCollection->expects(self::once())
            ->method('getItems')
            ->willReturn(array_filter([21 => $searchableAttribute]));
        $this->collectionFactoryMock->expects(self::exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($filterableAttributeCollection, $searchableAttributeCollection);

        $filterableAttribute->method('getAttributeCode')
            ->willReturn($filterableAttrCode);
        $filterableAttribute->method('getFrontendInput')
            ->willReturn($filterableAttrInput);
        $searchableAttribute->method('getAttributeCode')
            ->willReturn($searchableAttrCode);
        $searchableAttribute->method('getFrontendInput')
            ->willReturn($searchableAttrInput);

        $config = $this->model->read();
        self::assertNotEmpty($config['ProductAttributeFilterInput']);
        self::assertCount(count($fieldsType), $config['ProductAttributeFilterInput']['fields']);
        foreach ($fieldsType as $attrCode => $fieldType) {
            self::assertEquals($fieldType, $config['ProductAttributeFilterInput']['fields'][$attrCode]['type']);
        }
    }

    public function readDataProvider(): array
    {
        return [
            [
                'price',
                'price',
                'sku',
                'text',
                [
                    'price' => 'FilterRangeTypeInput',
                    'sku' => 'FilterEqualTypeInput',
                ],
            ],
            [
                'date_attr',
                'date',
                'datetime_attr',
                'datetime',
                [
                    'date_attr' => 'FilterRangeTypeInput',
                    'datetime_attr' => 'FilterRangeTypeInput',
                ],
            ],
            [
                'select_attr',
                'select',
                'multiselect_attr',
                'multiselect',
                [
                    'select_attr' => 'FilterEqualTypeInput',
                    'multiselect_attr' => 'FilterEqualTypeInput',
                ],
            ],
            [
                'text_attr',
                'text',
                'textarea_attr',
                'textarea',
                [
                    'text_attr' => 'FilterMatchTypeInput',
                    'textarea_attr' => 'FilterMatchTypeInput',
                ],
            ],
            [
                'boolean_attr',
                'boolean',
                'boolean_attr',
                'boolean',
                [
                    'boolean_attr' => 'FilterEqualTypeInput',
                ],
            ],
        ];
    }
}
