<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Creates simple Catalog Rule with the following data:
 * active, applied to all products, without time limits, with 50% off for all customers
 */
/** @var \Magento\CatalogRule\Model\Rule $rule */
$catalogRule = Bootstrap::getObjectManager()->get(\Magento\CatalogRule\Model\RuleFactory::class)->create();
$catalogRule->loadPost(
    [
        'name' => 'Test Catalog Rule 50% off',
        'is_active' => '1',
        'stop_rules_processing' => 0,
        'website_ids' => [2],
        'customer_group_ids' => [0, 1],
        'discount_amount' => 50,
        'simple_action' => 'by_percent',
        'from_date' => '',
        'to_date' => '',
        'sort_order' => 0,
        'sub_is_enable' => 0,
        'sub_discount_amount' => 0,
        'conditions' => [],
    ]
);
$catalogRule->save();
/** @var \Magento\CatalogRule\Model\Indexer\IndexBuilder $indexBuilder */
$indexBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\CatalogRule\Model\Indexer\IndexBuilder::class);
$indexBuilder->reindexFull();
