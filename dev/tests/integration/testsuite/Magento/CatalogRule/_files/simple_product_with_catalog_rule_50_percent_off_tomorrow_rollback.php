<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var CatalogRuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(CatalogRuleRepositoryInterface::class);
/** @var IndexBuilder $indexBuilder */
$indexBuilder = $objectManager->get(IndexBuilder::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
try {
    $productRepository->deleteById('simple');
} catch (NoSuchEntityException $e) {
    //already removed
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

/** @var Rule $catalogRuleResource */
$catalogRuleResource = $objectManager->create(Rule::class);
//Retrieve rule by name
$select = $catalogRuleResource->getConnection()
    ->select()
    ->from($catalogRuleResource->getMainTable(), 'rule_id')
    ->where('name = ?', 'Test Catalog Rule 50% off tomorrow');
$ruleId = $catalogRuleResource->getConnection()->fetchOne($select);

try {
    $ruleRepository->deleteById($ruleId);
} catch (CouldNotDeleteException $ex) {
    //Nothing to remove
}

$indexBuilder->reindexFull();

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_website_with_two_stores_rollback.php');
