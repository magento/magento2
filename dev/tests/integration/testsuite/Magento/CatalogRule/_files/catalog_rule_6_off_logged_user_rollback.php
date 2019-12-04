<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var IndexBuilder $indexBuilder */
$indexBuilder = $objectManager->get(IndexBuilder::class);
/** @var Rule $catalogRuleResource */
$catalogRuleResource = $objectManager->create(Rule::class);
$connection = $catalogRuleResource->getConnection();
/** @var CatalogRuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->create(CatalogRuleRepositoryInterface::class);

//Retrieve rule id by name
$select = $connection->select();
$select->from($catalogRuleResource->getMainTable(), 'rule_id');
$select->where('name = ?', 'Test Catalog Rule for logged user');
$ruleId = $connection->fetchOne($select);

try {
    $ruleRepository->deleteById($ruleId);
} catch (NoSuchEntityException $e) {
    //Rule already removed.
}
$indexBuilder->reindexFull();
