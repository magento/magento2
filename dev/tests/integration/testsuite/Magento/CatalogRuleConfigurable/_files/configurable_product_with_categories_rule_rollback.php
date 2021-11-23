<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()
    ->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable_12345_rollback.php');

$objectManager = Bootstrap::getObjectManager();

$ruleCollection = $objectManager->create(Collection::class)
    ->addFieldToFilter('name', ['eq' => 'Categories rule for configurable product'])
    ->setPageSize(1);
$rule = $ruleCollection->getFirstItem();
if ($rule->getId()) {
    $ruleRepository = $objectManager->create(CatalogRuleRepositoryInterface::class);
    $ruleRepository->delete($rule);
}

$indexBuilder = $objectManager->get(IndexBuilder::class);
$indexBuilder->reindexFull();
