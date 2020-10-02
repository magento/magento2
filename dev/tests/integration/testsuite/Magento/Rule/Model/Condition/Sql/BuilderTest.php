<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Model\Condition\Sql;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogWidget\Model\RuleFactory;
use Magento\CatalogWidget\Model\Rule\Condition\Combine as CombineCondition;
use Magento\CatalogWidget\Model\Rule\Condition\Product as ProductCondition;

/**
 * Test for Magento\Rule\Model\Condition\Sql\Builder
 */
class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Builder
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->create(Builder::class);
    }

    /**
     * @return void
     */
    public function testAttachConditionToCollection(): void
    {
        /** @var ProductCollectionFactory $collectionFactory */
        $collectionFactory = Bootstrap::getObjectManager()->create(ProductCollectionFactory::class);
        $collection = $collectionFactory->create();

        /** @var RuleFactory $ruleFactory */
        $ruleFactory = Bootstrap::getObjectManager()->create(RuleFactory::class);
        $rule = $ruleFactory->create();

        $ruleConditionArray = [
            'conditions' => [
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
                    'value' => ' :(  ,  :) ',
                ]
            ],
        ];

        $rule->loadPost($ruleConditionArray);
        $this->model->attachConditionToCollection($collection, $rule->getConditions());

        $whereString = "/\(category_id IN \('3'\).+\(IFNULL\(`e`\.`entity_id`,.+\) = '2017-09-15 00:00:00'\)"
            . ".+ORDER BY \(FIELD\(`e`.`sku`, ':\(', ':\)'\)\)/";
        $this->assertEquals(1, preg_match($whereString, $collection->getSelectSql(true)));
    }
}
