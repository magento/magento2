<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Creates simple Catalog Rule with the following data:
 * active, applied to all products, without time limits, with 50% off for registered customer groups
 */
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\Rule;
use Magento\Customer\Model\GroupManagement;
use Magento\TestFramework\Helper\Bootstrap;

/** @var $banner Rule */
$catalogRule = Bootstrap::getObjectManager()->create(
    Rule::class
);

$catalogRule
    ->setIsActive(1)
    ->setName('Test Catalog Rule With 50 Percent Off')
    ->setCustomerGroupIds('1')
    ->setDiscountAmount(50)
    ->setWebsiteIds([0 => 1])
    ->setSimpleAction('by_percent')
    ->setStopRulesProcessing(false)
    ->setSortOrder(0)
    ->setSubIsEnable(0)
    ->setSubDiscountAmount(0)
    ->save();

/** @var IndexBuilder $indexBuilder */
$indexBuilder = Bootstrap::getObjectManager()
    ->get(IndexBuilder::class);
$indexBuilder->reindexFull();
sleep(1);
