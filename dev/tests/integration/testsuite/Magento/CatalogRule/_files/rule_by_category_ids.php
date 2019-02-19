<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\CatalogRule\Model\Rule $rule */
$rule = Bootstrap::getObjectManager()->get(\Magento\CatalogRule\Model\RuleFactory::class)->create();
$rule->loadPost([
    'name' => 'test_category_rule',
    'is_active' => '1',
    'stop_rules_processing' => 0,
    'website_ids' => [1],
    'customer_group_ids' => [0, 1],
    'discount_amount' => 50,
    'simple_action' => 'by_percent',
    'from_date' => '',
    'to_date' => '',
    'sort_order' => 0,
    'sub_is_enable' => 0,
    'sub_discount_amount' => 0,
    'conditions' => [
        '1' => [
            'type' => Combine::class,
            'aggregator' => 'all',
            'value' => '1',
            'new_child' => '',
        ],
        '1--1' => [
            'type' => Product::class,
            'attribute' => 'category_ids',
            'operator' => '==',
            'value' => '10',
        ],
    ],
]);

/** @var  CatalogRuleRepositoryInterface $catalogRuleRepository */
$catalogRuleRepository = Bootstrap::getObjectManager()->get(CatalogRuleRepositoryInterface::class);
$catalogRuleRepository->save($rule);
