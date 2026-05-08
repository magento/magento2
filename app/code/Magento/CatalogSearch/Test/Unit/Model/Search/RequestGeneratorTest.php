<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorResolver;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogSearch\Model\Search\RequestGenerator
 */
class RequestGeneratorTest extends TestCase
{
    use MockCreationTrait;

    /** @var RequestGenerator */
    protected $object;

    /** @var  CollectionFactory|MockObject */
    protected $productAttributeCollectionFactory;

    protected function setUp(): void
    {
        $this->productAttributeCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $generatorResolver = $this->createPartialMock(
            GeneratorResolver::class,
            ['getGeneratorForType']
        );
        $generator = $this->createPartialMock(
            GeneratorInterface::class,
            ['getFilterData', 'getAggregationData']
        );
        $generator->method('getFilterData')
            ->willReturn(['some filter data goes here']);
        $generator->method('getAggregationData')
            ->willReturn(['some aggregation data goes here']);
        $generatorResolver->method('getGeneratorForType')
            ->willReturn($generator);

        $this->object = new RequestGenerator(
            $this->productAttributeCollectionFactory,
            $generatorResolver
        );
    }

    /**
     * @return array
     */
    public static function attributesProvider()
    {
        return [
            [
                [
                    'quick_search_container' => ['queries' => 1, 'filters' => 0, 'aggregations' => 0],
                    'advanced_search_container' => ['queries' => 0, 'filters' => 0, 'aggregations' => 0],
                    'catalog_view_container' => ['queries' => 0, 'filters' => 0, 'aggregations' => 0]
                ],
                ['sku', 'static', 0, 0, 1 ]
            ],
            [
                [
                    'quick_search_container' => ['queries' => 0, 'filters' => 0, 'aggregations' => 0],
                    'advanced_search_container' => ['queries' => 0, 'filters' => 0, 'aggregations' => 0],
                    'catalog_view_container' => ['queries' => 0, 'filters' => 0, 'aggregations' => 0]
                ],
                ['price', 'static', 1, 0 ,1]
            ],
            [
                [
                    'quick_search_container' => ['queries' => 1, 'filters' => 0, 'aggregations' => 0],
                    'advanced_search_container' => ['queries' => 2, 'filters' => 0, 'aggregations' => 0],
                    'catalog_view_container' => ['queries' => 0, 'filters' => 0, 'aggregations' => 0]
                ],
                ['name', 'text', 0, 0, 1]
            ],
            [
                [
                    'quick_search_container' => ['queries' => 1, 'filters' => 0, 'aggregations' => 0],
                    'advanced_search_container' => ['queries' => 2, 'filters' => 0, 'aggregations' => 0],
                    'catalog_view_container' => ['queries' => 0, 'filters' => 0, 'aggregations' => 0]
                ],
                ['name2', 'text', 0, 0, 1]
            ],
            [
                [
                    'quick_search_container' => ['queries' => 3, 'filters' => 1, 'aggregations' => 1],
                    'advanced_search_container' => ['queries' => 2, 'filters' => 1, 'aggregations' => 0],
                    'catalog_view_container' => ['queries' => 2, 'filters' => 1, 'aggregations' => 1]
                ],
                ['date', 'decimal', 1, 1, 1]
            ],
            [
                [
                    'quick_search_container' => ['queries' => 3, 'filters' => 1, 'aggregations' => 1],
                    'advanced_search_container' => ['queries' => 0, 'filters' => 0, 'aggregations' => 0],
                    'catalog_view_container' => ['queries' => 0, 'filters' => 0, 'aggregations' => 0]
                ],
                ['attr_int', 'int', 0, 1, 0]
            ],
            [
                [
                    'quick_search_container' => ['queries' => 2, 'filters' => 1, 'aggregations' => 1],
                    'advanced_search_container' => ['queries' => 0, 'filters' => 0, 'aggregations' => 0],
                    'catalog_view_container' => ['queries' => 0, 'filters' => 0, 'aggregations' => 0],
                ],
                ['custom_price_attr', 'price', 0, 1, 0],
            ],
        ];
    }

    /**
     * @param array $countResult
     * @param $attributeOptions
     */
    #[DataProvider('attributesProvider')]
    public function testGenerate($countResult, $attributeOptions)
    {
        $collection = $this->createMock(Collection::class);
        $collection->method('getIterator')
            ->willReturn(
                new \ArrayIterator(
                    [
                        $this->createAttributeMock($attributeOptions),
                    ]
                )
            );
        $collection->method('addFieldToFilter')
            ->with(
                ['is_searchable', 'is_visible_in_advanced_search', 'is_filterable', 'is_filterable_in_search'],
                [1, 1, [1, 2], 1]
            )->willReturnSelf();

        $this->productAttributeCollectionFactory->method('create')
            ->willReturn($collection);
        $result = $this->object->generate();

        $this->assertEquals(
            $countResult['quick_search_container']['queries'],
            $this->countVal($result['quick_search_container']['queries']),
            'Queries count for "quick_search_container" doesn\'t match'
        );
        $this->assertEquals(
            $countResult['advanced_search_container']['queries'],
            $this->countVal($result['advanced_search_container']['queries']),
            'Queries count for "advanced_search_container" doesn\'t match'
        );
        $this->assertEquals(
            $countResult['advanced_search_container']['filters'],
            $this->countVal($result['advanced_search_container']['filters']),
            'Filters count for "advanced_search_container" doesn\'t match'
        );
        $this->assertEquals(
            $countResult['catalog_view_container']['queries'],
            $this->countVal($result['catalog_view_container']['queries']),
            'Queries count for "catalog_view_container" doesn\'t match'
        );
        foreach ($result as $key => $value) {
            if (isset($value['queries'][$key]['queryReference'])) {
                foreach ($value['queries'][$key]['queryReference'] as $reference) {
                    $this->assertEquals(
                        'must',
                        $reference['clause']
                    );
                }
            }
        }
    }

    /**
     * Create attribute mock
     *
     * @param $attributeOptions
     * @return \Magento\Catalog\Model\Entity\Attribute|MockObject
     */
    private function createAttributeMock($attributeOptions)
    {
        /** @var \Magento\Catalog\Model\Entity\Attribute|MockObject $attribute */
        $attribute = $this->createPartialMockWithReflection(
            AttributeResourceModel::class,
            [
                'getAttributeCode',
                'getBackendType',
                'getIsVisibleInAdvancedSearch',
                'getFrontendInput',
                'getData',
                'getIsSearchable',
                'getSearchWeight',
            ]
        );
        $attribute->method('getAttributeCode')
            ->willReturn($attributeOptions[0]);
        $attribute->method('getBackendType')
            ->willReturn($attributeOptions[1]);
        $attribute->method('getFrontendInput')
            ->willReturn($attributeOptions[1]);

        $attribute->method('getSearchWeight')
            ->willReturn(1);

        $attribute->method('getIsVisibleInAdvancedSearch')
            ->willReturn($attributeOptions[4]);

        $attribute->method('getData')
            ->willReturnMap(
                [
                    ['is_filterable', null, $attributeOptions[2]],
                    ['is_filterable_in_search', null, $attributeOptions[3]],
                ]
            );

        $attribute->method('getIsSearchable')
            ->willReturn(1);

        return $attribute;
    }

    /**
     * @param $value
     * @return int|void
     */
    private function countVal(&$value)
    {
        return !empty($value) ? count($value) : 0;
    }
}
