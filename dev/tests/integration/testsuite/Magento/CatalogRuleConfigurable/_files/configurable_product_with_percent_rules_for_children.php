<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Customer\Model\Group;

require __DIR__ . '/configurable_product_with_percent_rule.php';

/** @var CatalogRuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(CatalogRuleRepositoryInterface::class);
/** @var Rule $firstRule */
$ruleFactory = $objectManager->get(RuleFactory::class);

$firstRule = $ruleFactory->create();
$firstRule->loadPost(
    [
        'name' => 'Percent rule for first simple product',
        'is_active' => '1',
        'stop_rules_processing' => 0,
        'website_ids' => [$websiteRepository->get('base')->getId()],
        'customer_group_ids' => Group::NOT_LOGGED_IN_ID,
        'discount_amount' => 10,
        'simple_action' => 'by_percent',
        'from_date' => '',
        'to_date' => '',
        'sort_order' => 0,
        'sub_is_enable' => 0,
        'sub_discount_amount' => 0,
        'conditions' => [
            '1' => ['type' => Combine::class, 'aggregator' => 'all', 'value' => '1', 'new_child' => ''],
            '1--1' => ['type' => Product::class, 'attribute' => 'sku', 'operator' => '==', 'value' => 'simple_10'],
        ],
    ]
);
$ruleRepository->save($firstRule);

$secondRule = $ruleFactory->create();
$secondRule->loadPost(
    [
        'name' => 'Percent rule for second simple product',
        'is_active' => '1',
        'stop_rules_processing' => 0,
        'website_ids' => [$websiteRepository->get('base')->getId()],
        'customer_group_ids' => Group::NOT_LOGGED_IN_ID,
        'discount_amount' => 20,
        'simple_action' => 'by_percent',
        'from_date' => '',
        'to_date' => '',
        'sort_order' => 0,
        'sub_is_enable' => 0,
        'sub_discount_amount' => 0,
        'conditions' => [
            '1' => ['type' => Combine::class, 'aggregator' => 'all', 'value' => '1', 'new_child' => ''],
            '1--1' => ['type' => Product::class, 'attribute' => 'sku', 'operator' => '==', 'value' => 'simple_20'],
        ],
    ]
);
$ruleRepository->save($secondRule);
