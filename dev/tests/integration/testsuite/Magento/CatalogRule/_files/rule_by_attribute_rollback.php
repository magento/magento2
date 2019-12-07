<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\CatalogRule\Model\ResourceModel\Rule $catalogRuleResource */
$catalogRuleResource = $objectManager->create(\Magento\CatalogRule\Model\ResourceModel\Rule::class);

//Retrieve rule id by name
$select = $catalogRuleResource->getConnection()->select();
$select->from($catalogRuleResource->getMainTable(), 'rule_id');
$select->where('name = ?', 'test_rule');
$ruleId = $catalogRuleResource->getConnection()->fetchOne($select);

try {
    /** @var \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface $ruleRepository */
    $ruleRepository = $objectManager->create(\Magento\CatalogRule\Api\CatalogRuleRepositoryInterface::class);
    $ruleRepository->deleteById($ruleId);
} catch (\Exception $ex) {
    //Nothing to remove
}
/** @var \Magento\CatalogRule\Model\Indexer\IndexBuilder $indexBuilder */
$indexBuilder = $objectManager->get(\Magento\CatalogRule\Model\Indexer\IndexBuilder::class);
$indexBuilder->reindexFull();
