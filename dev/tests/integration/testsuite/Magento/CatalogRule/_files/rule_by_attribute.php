<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\CatalogRule\Model\Rule $rule */
$rule = Bootstrap::getObjectManager()->get('Magento\CatalogRule\Model\RuleFactory')->create();
$rule->loadPost([
    'name' => 'test_rule',
    'is_active' => '1',
    'website_ids' => [1],
    'customer_group_ids' => [0, 1],
    'discount_amount' => 2,
    'simple_action' => 'by_percent',
    'from_date' => '',
    'to_date' => '',
    'conditions' => [
        '1' => [
            'type' => 'Magento\CatalogRule\Model\Rule\Condition\Combine',
            'aggregator' => 'all',
            'value' => '1',
            'new_child' => '',
        ],
        '1--1' => [
            'type' => 'Magento\CatalogRule\Model\Rule\Condition\Product',
            'attribute' => 'test_attribute',
            'operator' => '==',
            'value' => 'test_attribute_value',
        ],
    ],
]);
$rule->save();
