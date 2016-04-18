<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Creates simple Catalog Rule with the following data:
 * active, applied to all products, without time limits, with 10% off for Not Logged In Customers
 */

/** @var $banner \Magento\CatalogRule\Model\Rule */
$catalogRule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\CatalogRule\Model\Rule');

$catalogRule
    ->setIsActive(1)
    ->setName('Test Catalog Rule')
    ->setCustomerGroupIds(\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID)
    ->setDiscountAmount(10)
    ->setWebsiteIds([0 => 1])
    ->setSimpleAction('by_percent')
    ->save();

/** @var \Magento\CatalogRule\Model\Indexer\IndexBuilder $indexBuilder */
$indexBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('Magento\CatalogRule\Model\Indexer\IndexBuilder');
$indexBuilder->reindexFull();
