<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\CatalogRule\Model\ResourceModel\Rule $catalogRuleResource */
$catalogRuleResource = $objectManager->create(\Magento\CatalogRule\Model\ResourceModel\Rule::class);

/** @var \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->create(\Magento\CatalogRule\Api\CatalogRuleRepositoryInterface::class);

foreach (['test_rule_one', 'test_rule_two'] as $ruleName) {
    //Retrieve rule id by name
    $select = $catalogRuleResource->getConnection()->select();
    $select->from($catalogRuleResource->getMainTable(), 'rule_id');
    $select->where('name = ?', $ruleName);
    $ruleId = $catalogRuleResource->getConnection()->fetchOne($select);

    try {
        $ruleRepository->deleteById($ruleId);
    } catch (\Exception $ex) {
        //Nothing to remove
    }
}

/** @var \Magento\CatalogRule\Model\Indexer\IndexBuilder $indexBuilder */
$indexBuilder = $objectManager->get(\Magento\CatalogRule\Model\Indexer\IndexBuilder::class);
$indexBuilder->reindexFull();
