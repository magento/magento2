<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Model\Condition\Sql;

use Magento\TestFramework\Helper\Bootstrap;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    private $model;

    protected function setUp()
    {
        $this->model = Bootstrap::getObjectManager()->create(\Magento\Rule\Model\Condition\Sql\Builder::class);
    }

    public function testAttachConditionToCollection()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory */
        $collectionFactory = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class
        );
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $collectionFactory->create();

        /** @var \Magento\CatalogWidget\Model\RuleFactory $ruleFactory */
        $ruleFactory = Bootstrap::getObjectManager()->create(\Magento\CatalogWidget\Model\RuleFactory::class);
        /** @var \Magento\CatalogWidget\Model\Rule $rule */
        $rule = $ruleFactory->create();

        $ruleConditionArray = [
            'conditions' => [
                '1' => [
                    'type' => \Magento\CatalogWidget\Model\Rule\Condition\Combine::class,
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => ''
                ],
                '1--1' => [
                    'type' => \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
                    'attribute' => 'category_ids',
                    'operator' => '==',
                    'value' => '3'
                ],
                '1--2' => [
                    'type' => \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
                    'attribute' => 'special_to_date',
                    'operator' => '==',
                    'value' => '2017-09-15'
                ],
            ]
        ];


        $rule->loadPost($ruleConditionArray);
        $this->model->attachConditionToCollection($collection, $rule->getConditions());

        $whereString = 'WHERE (category_id IN (\'3\')))) AND(IFNULL(`e`.`entity_id`, 0) = \'2017-09-15\') ))';
        $this->assertNotFalse(strpos($collection->getSelectSql(true), $whereString));
    }
}
