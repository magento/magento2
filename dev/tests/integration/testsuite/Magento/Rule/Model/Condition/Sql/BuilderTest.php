<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Rule\Model\Condition\Sql;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Catalog\Test\Fixture\MultiselectAttribute;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogWidget\Model\RuleFactory;
use Magento\CatalogWidget\Model\Rule\Condition\Combine as CombineCondition;
use Magento\CatalogWidget\Model\Rule\Condition\Product as ProductCondition;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Rule\Model\Condition\Sql\Builder
 */
class BuilderTest extends TestCase
{
    /**
     * @var Builder|null
     */
    private ?Builder $model;

    /**
     * @var DataFixtureStorage|null
     */
    private ?DataFixtureStorage $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->create(Builder::class);
        $this->fixtures = BootstrapHelper::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @param array $conditions
     * @param string $expectedWhere
     * @param string $expectedOrder
     * @return void
     * @throws LocalizedException|\PHPUnit\Framework\MockObject\Exception
     * @dataProvider attachConditionToCollectionDataProvider
     */
    #[
        DataFixture(
            MultiselectAttribute::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'source_model' => null,
                'backend_model' => ArrayBackend::class,
                'attribute_code' => 'multi_select_attr',
                'is_visible_on_front' => true,
                'frontend_input' => 'multiselect',
                'backend_type' => 'text',
                'attribute_model' => Attribute::class,
                'options' => ['red', 'white']
            ],
            'multiselect'
        )
    ]
    public function testAttachConditionToCollection(
        array $conditions,
        string $expectedWhere,
        string $expectedOrder
    ): void {
        /** @var ProductCollectionFactory $collectionFactory */
        $collectionFactory = Bootstrap::getObjectManager()->create(ProductCollectionFactory::class);
        $collection = $collectionFactory->create();
        foreach ($conditions as $key => $condition) {
            if (isset($condition['attribute']) && $condition['attribute'] === 'multi_select_attr') {
                $multiselectAttributeOptionIds = [
                    $this->fixtures->get('multiselect')->getData('red'),
                    $this->fixtures->get('multiselect')->getData('white')
                ];
                $expectedWhere = str_replace(["red", "white"], $multiselectAttributeOptionIds, $expectedWhere);

                $conditions[$key]['value'] = $multiselectAttributeOptionIds;
            }
        }

        /** @var RuleFactory $ruleFactory */
        $ruleFactory = Bootstrap::getObjectManager()->create(RuleFactory::class);
        $rule = $ruleFactory->create();

        $ruleConditionArray = [
            'conditions' => $conditions,
        ];

        $rule->loadPost($ruleConditionArray);
        foreach ($rule->getConditions()->getConditions() as $condition) {
            $condition->addToCollection($collection);
            if ($condition->getAttribute() === 'multi_select_attr') {
                $from = array_keys($collection->getSelectSql()->getPart('from'));
                $expectedWhere = str_replace('multi_select_attr', end($from), $expectedWhere);
            }
        }
        $this->model->attachConditionToCollection($collection, $rule->getConditions());

        $this->assertStringContainsString($expectedWhere, $collection->getSelectSql(true));
        $this->assertStringContainsString($expectedOrder, $collection->getSelectSql(true));
    }

    /**
     * @return array
     */
    public static function attachConditionToCollectionDataProvider(): array
    {
        return [
            [
                [
                    '1' => [
                        'type' => CombineCondition::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => [
                        'type' => ProductCondition::class,
                        'attribute' => 'category_ids',
                        'operator' => '==',
                        'value' => '3',
                    ],
                    '1--2' => [
                        'type' => ProductCondition::class,
                        'attribute' => 'special_to_date',
                        'operator' => '==',
                        'value' => '2017-09-15',
                    ],
                    '1--3' => [
                        'type' => ProductCondition::class,
                        'attribute' => 'sku',
                        'operator' => '()',
                        'value' => 'sku1,sku2,sku3,sku4,sku5',
                    ]
                ],
                "(((`e`.`entity_id` IN (SELECT `catalog_category_product`.`product_id` FROM " .
                "`catalog_category_product` WHERE (category_id IN ('3')))) " .
                "AND(IF(`at_special_to_date`.`value_id` > 0, `at_special_to_date`.`value`, " .
                "`at_special_to_date_default`.`value`) = '2017-09-15 00:00:00') " .
                "AND(`e`.`sku` IN ('sku1', 'sku2', 'sku3', 'sku4', 'sku5'))",
                "ORDER BY (FIELD(`e`.`sku`, 'sku1', 'sku2', 'sku3', 'sku4', 'sku5'))"
            ],
            [
                [
                    '1' => [
                        'type' => CombineCondition::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => [
                        'type' => ProductCondition::class,
                        'attribute' => 'category_ids',
                        'operator' => '==',
                        'value' => '3',
                    ],
                    '1--2' => [
                        'type' => ProductCondition::class,
                        'attribute' => 'sku',
                        'operator' => '()',
                        'value' => 'sku1,sku2,sku3',
                    ],
                    '1--3' => [
                        'type' => ProductCondition::class,
                        'attribute' => 'multi_select_attr',
                        'operator' => '{}',
                        'collected_attributes' => ['multiselect_attribute' => true],
                    ]
                ],
                "(((`e`.`entity_id` IN (SELECT `catalog_category_product`.`product_id` FROM " .
                "`catalog_category_product` WHERE (category_id IN ('3')))) " .
                "AND(`e`.`sku` IN ('sku1', 'sku2', 'sku3')) AND(`multi_select_attr`.`value` IN ('red', 'white') OR " .
                "(FIND_IN_SET ('red', `multi_select_attr`.`value`) > 0) OR " .
                "(FIND_IN_SET ('white', `multi_select_attr`.`value`) > 0))",
                "ORDER BY (FIELD(`e`.`sku`, 'sku1', 'sku2', 'sku3'))"
            ]
        ];
    }
}
