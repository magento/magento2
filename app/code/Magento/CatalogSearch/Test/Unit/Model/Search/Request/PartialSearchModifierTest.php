<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search\Request;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\Search\Request\PartialSearchModifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test "partial" search requests modifier
 */
class PartialSearchModifierTest extends TestCase
{
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
        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'addFieldToFilter'])
            ->getMock();
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
     * @dataProvider modifyDataProvider
     */
    public function testModify(array $attributes, array $requests, array $expected): void
    {
        $items = [];
        foreach ($attributes as $attribute) {
            $item = $this->getMockForAbstractClass(\Magento\Eav\Api\Data\AttributeInterface::class);
            $item->method('getAttributeCode')
                ->willReturn($attribute);
            $items[] = $item;
        }
        $reflectionProperty = new \ReflectionProperty($this->collection, '_items');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->collection, $items);
        $this->assertEquals($expected, $this->model->modify($requests));
    }

    /**
     * @return array
     */
    public function modifyDataProvider(): array
    {
        return [
            [
                [
                    'name',
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
                ]
            ]
        ];
    }
}
