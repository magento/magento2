<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\ResourceModel\Product;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Product;

// @codingStandardsIgnoreFile

class ConditionsToCollectionApplierTest extends \PHPUnit\Framework\TestCase
{
    private $objectManager;

    private $productCollectionFactory;

    private $conditionsToCollectionApplier;

    private $combinedConditionFactory;

    private $simpleConditionFactory;

    private $categoryCollectionFactory;

    private $setFactory;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->productCollectionFactory = $this->objectManager
            ->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);

        $this->conditionsToCollectionApplier = $this->objectManager
            ->get(\Magento\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier::class);

        $this->combinedConditionFactory = $this->objectManager
            ->get(\Magento\CatalogRule\Model\Rule\Condition\CombineFactory::class);

        $this->simpleConditionFactory = $this->objectManager
            ->get(\Magento\CatalogRule\Model\Rule\Condition\ProductFactory::class);

        $this->categoryCollectionFactory = $this->objectManager
            ->get(\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class);

        $this->setFactory = $this->objectManager
            ->get(\Magento\Eav\Model\Entity\Attribute\SetFactory::class);
    }

    /**
     * @magentoDataFixture Magento/CatalogRule/_files/conditions_to_collection/categories.php
     * @magentoDataFixture Magento/CatalogRule/_files/conditions_to_collection/attribute_sets.php
     * @magentoDataFixture Magento/CatalogRule/_files/conditions_to_collection/products.php
     *
     * @magentoDbIsolation disabled
     */
    public function testVariations()
    {
        foreach ($this->conditionProvider() as $variationName => $variationData) {
            $condition = $variationData['condition'];
            $expectedSkuList = $variationData['expected-sku'];

            $productCollection = $this->productCollectionFactory->create();
            $resultCollection = $this->conditionsToCollectionApplier
                ->applyConditionsToCollection($condition, $productCollection);

            $resultSkuList = array_map(
                function (Product $product) {
                    return $product->getSku();
                },
                array_values($resultCollection->getItems())
            );

            asort($expectedSkuList);
            asort($resultSkuList);

            $expectedSkuList = array_values($expectedSkuList);
            $resultSkuList = array_values($resultSkuList);

            $this->assertEquals($expectedSkuList, $resultSkuList, sprintf('%s failed', $variationName));
        }
    }

    /**
     *
     * @magentoDbIsolation disabled
     */
    public function testExceptionUndefinedRuleOperator()
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage('Undefined rule operator "====" passed in. Valid operators are: ==,!=,>=,<=,>,<,{},!{},(),!()');

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'all',
            'value' => 0,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '====',
                    'value' => 42,
                    'attribute' => 'attribute_set_id'
                ]
            ]
        ];

        $combineCondition = $this->getCombineConditionFromArray($conditions);

        $productCollection = $this->productCollectionFactory->create();
        $this->conditionsToCollectionApplier
            ->applyConditionsToCollection($combineCondition, $productCollection);
    }

    /**
     *
     * @magentoDbIsolation disabled
     */
    public function testExceptionUndefinedRuleAggregator()
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage('Undefined rule aggregator "olo-lo" passed in. Valid operators are: all,any');

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'olo-lo',
            'value' => 0,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => 42,
                    'attribute' => 'attribute_set_id'
                ]
            ]
        ];

        $combineCondition = $this->getCombineConditionFromArray($conditions);

        $productCollection = $this->productCollectionFactory->create();
        $this->conditionsToCollectionApplier
            ->applyConditionsToCollection($combineCondition, $productCollection);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function conditionProvider()
    {
        return [
            // test filter by category without children
            'variation 1' => [
                'condition' => $this->getConditionsForVariation1(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-7',
                    'simple-product-8'
                ]
            ],

            // test filter by root category
            'variation 2' => [
                'condition' => $this->getConditionsForVariation2(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-3',
                    'simple-product-4',
                    'simple-product-5',
                    'simple-product-6',
                    'simple-product-7',
                    'simple-product-8',
                    'simple-product-9',
                    'simple-product-10',
                    'simple-product-11',
                    'simple-product-12'
                ]
            ],

            // test filter by anchor category with children
            'variation 3' => [
                'condition' => $this->getConditionsForVariation3(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-3',
                    'simple-product-4',
                    'simple-product-5',
                    'simple-product-6',
                    'simple-product-9',
                    'simple-product-10',
                    'simple-product-11',
                    'simple-product-12'
                ]
            ],

            // test filter by non existing category
            'variation 4' => [
                'condition' => $this->getConditionsForVariation4(),
                'expected-sku' => []
            ],

            // test filter by sku
            'variation 5' => [
                'condition' => $this->getConditionsForVariation5(),
                'expected-sku' => ['simple-product-2']
            ],

            // test filter by attribute set
            'variation 6' => [
                'condition' => $this->getConditionsForVariation6(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-4',
                    'simple-product-7',
                    'simple-product-10'
                ]
            ],

            // test filter by product name
            'variation 7' => [
                'condition' => $this->getConditionsForVariation7(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-9',
                    'simple-product-12'
                ]
            ],

            // test filter by not existing attribute
            'variation 8' => [
                'condition' => $this->getConditionsForVariation8(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-3',
                    'simple-product-4',
                    'simple-product-5',
                    'simple-product-6',
                    'simple-product-7',
                    'simple-product-8',
                    'simple-product-9',
                    'simple-product-10',
                    'simple-product-11',
                    'simple-product-12',
                    'simple-product-13',
                ]
            ],

            // test filter by category with empty value
            'variation 9' => [
                'condition' => $this->getConditionsForVariation9(),
                'expected-sku' => []
            ],

            // test filter by sku with empty value
            'variation 10' => [
                'condition' => $this->getConditionsForVariation10(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-3',
                    'simple-product-4',
                    'simple-product-5',
                    'simple-product-6',
                    'simple-product-7',
                    'simple-product-8',
                    'simple-product-9',
                    'simple-product-10',
                    'simple-product-11',
                    'simple-product-12',
                    'simple-product-13',
                ]
            ],

            // test filter by name with empty value
            'variation 11' => [
                'condition' => $this->getConditionsForVariation11(),
                'expected-sku' => []
            ],

            // test filter by like condition
            'variation 12' => [
                'condition' => $this->getConditionsForVariation12(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-3',
                    'simple-product-4',
                    'simple-product-5',
                    'simple-product-6',
                    'simple-product-9',
                    'simple-product-10',
                    'simple-product-11',
                    'simple-product-12'
                ]
            ],

            // test filter with ALL aggregation
            'variation 13' => [
                'condition' => $this->getConditionsForVariation13(),
                'expected-sku' => [
                    'simple-product-7',
                    'simple-product-8'
                ]
            ],

            // test filter with ANY aggregation
            'variation 14' => [
                'condition' => $this->getConditionsForVariation14(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-3',
                    'simple-product-4',
                    'simple-product-7',
                    'simple-product-8'
                ]
            ],

            // test filter with array in product condition's value
            'variation 15' => [
                'condition' => $this->getConditionsForVariation15(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-3',
                    'simple-product-4',
                    'simple-product-7',
                    'simple-product-8'
                ]
            ],

            // test filter by multiple sku
            'variation 16' => [
                'condition' => $this->getConditionsForVariation16(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-5',
                    'simple-product-11'
                ]
            ],

            // test filter with multiple combined conditions
            'variation 17' => [
                'condition' => $this->getConditionsForVariation17(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-4',
                    'simple-product-8',
                    'simple-product-10'
                ]
            ],

            // test filter with multiply levels in conditions
            'variation 18' => [
                'condition' => $this->getConditionsForVariation18(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-3',
                    'simple-product-4',
                    'simple-product-5',
                    'simple-product-6',
                    'simple-product-10',
                    'simple-product-11'
                ]
            ],

            // test filter with empty conditions
            'variation 19' => [
                'condition' => $this->getConditionsForVariation19(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-3',
                    'simple-product-4',
                    'simple-product-5',
                    'simple-product-6',
                    'simple-product-7',
                    'simple-product-8',
                    'simple-product-9',
                    'simple-product-10',
                    'simple-product-11',
                    'simple-product-12',
                    'simple-product-13',
                ]
            ],

            // test filter for case "If ALL of these conditions are FALSE"
            'variation 20' => [
                'condition' => $this->getConditionsForVariation20(),
                'expected-sku' => [
                    'simple-product-2',
                    'simple-product-5',
                    'simple-product-8',
                    'simple-product-11'
                ]
            ],

            // test filter for case "If ANY of these conditions are FALSE"
            'variation 21' => [
                'condition' => $this->getConditionsForVariation21(),
                'expected-sku' => [
                    'simple-product-1',
                    'simple-product-2',
                    'simple-product-3',
                    'simple-product-4',
                    'simple-product-5',
                    'simple-product-6',
                    'simple-product-7',
                    'simple-product-8',
                    'simple-product-9',
                    'simple-product-10',
                    'simple-product-11',
                    'simple-product-12',
                    'simple-product-13',
                ]
            ],

            // test filter for case "If ALL/ANY of these conditions are FALSE" with multiple levels
            'variation 22' => [
                'condition' => $this->getConditionsForVariation22(),
                'expected-sku' => [
                    'simple-product-7',
                    'simple-product-8',
                    'simple-product-13',
                ]
            ],

            // test filter by multiple sku and "is not one of" condition
            'variation 23' => [
                'condition' => $this->getConditionsForVariation23(),
                'expected-sku' => [
                    'simple-product-3',
                    'simple-product-4',
                    'simple-product-6',
                    'simple-product-7',
                    'simple-product-8',
                    'simple-product-9',
                    'simple-product-11',
                    'simple-product-12',
                    'simple-product-13',
                ]
            ],
        ];
    }

    private function getConditionsForVariation1()
    {
        $category2Name = 'Category 2';

        $category2Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category2Name)
            ->getAllIds();

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => implode(',', $category2Id),
                    'attribute' => 'category_ids'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation2()
    {
        $categoryName = 'Default Category';

        $categoryId = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $categoryName)
            ->getAllIds();

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => implode(',', $categoryId),
                    'attribute' => 'category_ids'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation3()
    {
        $category1Name = 'Category 1';

        $category1Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category1Name)
            ->getAllIds();

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => implode(',', $category1Id),
                    'attribute' => 'category_ids'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation4()
    {
        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => implode(',', [308567758103]),
                    'attribute' => 'category_ids'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation5()
    {
        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '{}',
                    'value' => 'product-2',
                    'attribute' => 'sku'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation6()
    {
        $attrSet = $this->setFactory->create()
            ->load('Super Powerful Muffins', 'attribute_set_name');

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => $attrSet->getId(),
                    'attribute' => 'attribute_set_id'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation7()
    {
        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '{}',
                    'value' => 'Sale',
                    'attribute' => 'name'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation8()
    {
        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => 'Sale',
                    'attribute' => 'absolutely_random_attribute_name'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation9()
    {
        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => '',
                    'attribute' => 'category_ids'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation10()
    {
        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => '',
                    'attribute' => 'sku'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation11()
    {
        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => '',
                    'attribute' => 'name'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation12()
    {
        $category1Name = 'Category 1';

        $category1Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category1Name)
            ->getAllIds();

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '{}',
                    'value' => implode(',', $category1Id),
                    'attribute' => 'category_ids'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation13()
    {
        $category3Name = 'Category 3';

        $category3Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category3Name)
            ->getAllIds();

        $category2Name = 'Category 2';

        $category2Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category2Name)
            ->getAllIds();

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'all',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '{}',
                    'value' => implode(',', $category3Id),
                    'attribute' => 'category_ids'
                ],
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => implode(',', $category2Id),
                    'attribute' => 'category_ids'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation14()
    {
        $category3Name = 'Category 3';

        $category3Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category3Name)
            ->getAllIds();

        $category2Name = 'Category 2';

        $category2Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category2Name)
            ->getAllIds();

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '{}',
                    'value' => implode(',', $category3Id),
                    'attribute' => 'category_ids'
                ],
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => implode(',', $category2Id),
                    'attribute' => 'category_ids'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation15()
    {
        $category3Name = 'Category 3';

        $category3Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category3Name)
            ->getAllIds();

        $category2Name = 'Category 2';

        $category2Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category2Name)
            ->getAllIds();

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'all',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '{}',
                    'value' => [$category3Id[0], $category2Id[0]],
                    'attribute' => 'category_ids'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation16()
    {
        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '()',
                    'value' => 'simple-product-1,simple-product-5,simple-product-11',
                    'attribute' => 'sku'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation17()
    {
        $category1Name = 'Category 1';

        $category1Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category1Name)
            ->getAllIds();

        $category2Name = 'Category 2';

        $category2Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category2Name)
            ->getAllIds();

        $attributeSetMuffins = $this->setFactory->create()
            ->load('Super Powerful Muffins', 'attribute_set_name');

        $attributeSetRangers = $this->setFactory->create()
            ->load('Banana Rangers', 'attribute_set_name');

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
                    'aggregator' => 'all',
                    'value' => 1,
                    'conditions' => [
                        [
                            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                            'operator' => '==',
                            'value' => implode(',', $category1Id),
                            'attribute' => 'category_ids'
                        ],
                        [
                            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                            'operator' => '==',
                            'value' => $attributeSetMuffins->getId(),
                            'attribute' => 'attribute_set_id'
                        ]
                    ]
                ],
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
                    'aggregator' => 'all',
                    'value' => 1,
                    'conditions' => [
                        [
                            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                            'operator' => '==',
                            'value' => implode(',', $category2Id),
                            'attribute' => 'category_ids'
                        ],
                        [
                            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                            'operator' => '==',
                            'value' => $attributeSetRangers->getId(),
                            'attribute' => 'attribute_set_id'
                        ]
                    ]
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation18()
    {
        $category1Name = 'Category 1';

        $category1Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category1Name)
            ->getAllIds();

        $attributeSetMuffins = $this->setFactory->create()
            ->load('Super Powerful Muffins', 'attribute_set_name');

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
                    'aggregator' => 'all',
                    'value' => 1,
                    'conditions' => [
                        [
                            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                            'operator' => '==',
                            'value' => implode(',', $category1Id),
                            'attribute' => 'category_ids'
                        ],
                        [
                            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
                            'aggregator' => 'any',
                            'value' => 1,
                            'conditions' => [
                                [
                                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                                    'operator' => '!{}',
                                    'value' => '(Sale)',
                                    'attribute' => 'name'
                                ],
                                [
                                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                                    'operator' => '==',
                                    'value' => $attributeSetMuffins->getId(),
                                    'attribute' => 'attribute_set_id'
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation19()
    {
        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'all',
            'value' => 0,
            'conditions' => []
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation20()
    {
        $attributeSetMuffins = $this->setFactory->create()
            ->load('Super Powerful Muffins', 'attribute_set_name');

        $attributeSetGuardians = $this->setFactory->create()
            ->load('Guardians of the Refrigerator', 'attribute_set_name');

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'all',
            'value' => 0,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => $attributeSetMuffins->getId(),
                    'attribute' => 'attribute_set_id'
                ],
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => $attributeSetGuardians->getId(),
                    'attribute' => 'attribute_set_id'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation21()
    {
        $attributeSetMuffins = $this->setFactory->create()
            ->load('Super Powerful Muffins', 'attribute_set_name');

        $attributeSetGuardians = $this->setFactory->create()
            ->load('Guardians of the Refrigerator', 'attribute_set_name');

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'any',
            'value' => 0,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => $attributeSetMuffins->getId(),
                    'attribute' => 'attribute_set_id'
                ],
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '==',
                    'value' => $attributeSetGuardians->getId(),
                    'attribute' => 'attribute_set_id'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation22()
    {
        $category1Name = 'Category 1';

        $category1Id = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', $category1Name)
            ->getAllIds();

        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'all',
            'value' => 0,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
                    'aggregator' => 'all',
                    'value' => 1,
                    'conditions' => [
                        [
                            'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                            'operator' => '==',
                            'value' => implode(',', $category1Id),
                            'attribute' => 'category_ids'
                        ]
                    ]
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getConditionsForVariation23()
    {
        $conditions = [
            'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
            'aggregator' => 'all',
            'value' => 1,
            'conditions' => [
                [
                    'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                    'operator' => '!()',
                    'value' => 'simple-product-1, simple-product-2, simple-product-5, simple-product-10',
                    'attribute' => 'sku'
                ]
            ]
        ];

        return $this->getCombineConditionFromArray($conditions);
    }

    private function getCombineConditionFromArray(array $data)
    {
        $combinedCondition = $this->combinedConditionFactory->create();
        $combinedCondition->setPrefix('conditions');
        $combinedCondition->loadArray($data);

        return $combinedCondition;
    }
}
