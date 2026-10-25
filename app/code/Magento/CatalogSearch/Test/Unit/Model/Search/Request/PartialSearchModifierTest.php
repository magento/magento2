<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search\Request;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\Search\Request\PartialSearchModifier;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test "partial" search requests modifier
 */
class PartialSearchModifierTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Collection|MockObject
     */
    private $collection;

    /**
     * @var PartialSearchModifier
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $collectionFactory = $this->createMock(CollectionFactory::class);
        $this->collection = $this->createPartialMock(
            Collection::class,
            ['load', 'addFieldToFilter']
        );
        $collectionFactory->method('create')
            ->willReturn($this->collection);
        $this->model = new PartialSearchModifier($collectionFactory);
    }

    /**
     * Test that not searchable attributes are removed from the request
     *
     * @param array $attributes
     * @param array $requests
     * @param array $expected
     */
    #[DataProvider('modifyDataProvider')]
    public function testModify(array $attributes, array $requests, array $expected): void
    {
        $items = [];
        $searchWeight = 10;
        foreach ($attributes as $attribute) {
            $item = $this->createPartialMockWithReflection(
                Attribute::class,
                ['getSearchWeight', 'getAttributeCode']
            );
            $item->method('getAttributeCode')
                ->willReturn($attribute);
            $item->method('getSearchWeight')
                ->willReturn($searchWeight);
            $items[] = $item;
        }
        $reflectionProperty = new \ReflectionProperty($this->collection, '_items');
        $reflectionProperty->setValue($this->collection, $items);
        $this->assertEquals($expected, $this->model->modify($requests));
    }

    /**
     * @return array
     */
    public static function modifyDataProvider(): array
    {
        return [
            [
                [
                    'name',
                    'sku',
                ],
                [
                    'search_1' => [
                        'filters' => [
                            'category_filter' => [
                                'name' => 'category_filter',
                                'field' => 'category_ids',
                                'value' => '$category_ids$',
                            ]
                        ],
                        'queries' => [
                            'partial_search' => [
                                'name' => 'partial_search',
                                'value' => '$search_term$',
                                'match' => [
                                    [
                                        'field' => '*'
                                    ],
                                    [
                                        'field' => 'sku',
                                        'matchCondition' => 'match_phrase_prefix',
                                    ],
                                    [
                                        'field' => 'name',
                                        'matchCondition' => 'match_phrase_prefix',
                                    ],
                                ]
                            ]
                        ]
                    ],
                    'search_2' => [
                        'filters' => [
                            'category_filter' => [
                                'name' => 'category_filter',
                                'field' => 'category_ids',
                                'value' => '$category_ids$',
                            ]
                        ]
                    ]
                ],
                [
                    'search_1' => [
                        'filters' => [
                            'category_filter' => [
                                'name' => 'category_filter',
                                'field' => 'category_ids',
                                'value' => '$category_ids$',
                            ]
                        ],
                        'queries' => [
                            'partial_search' => [
                                'name' => 'partial_search',
                                'value' => '$search_term$',
                                'match' => [
                                    [
                                        'field' => '*'
                                    ],
                                    [
                                        'field' => 'sku',
                                        'matchCondition' => 'match_phrase_prefix',
                                        'boost' => 10
                                    ],
                                    [
                                        'field' => 'name',
                                        'matchCondition' => 'match_phrase_prefix',
                                        'boost' => 10
                                    ],
                                ]
                            ]
                        ]
                    ],
                    'search_2' => [
                        'filters' => [
                            'category_filter' => [
                                'name' => 'category_filter',
                                'field' => 'category_ids',
                                'value' => '$category_ids$',
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
