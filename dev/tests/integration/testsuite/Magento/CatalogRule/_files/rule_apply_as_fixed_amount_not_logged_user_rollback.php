<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var IndexBuilder $indexBuilder */
$indexBuilder = $objectManager->get(IndexBuilder::class);
/** @var CatalogRuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->create(CatalogRuleRepositoryInterface::class);
/** @var CollectionFactory $ruleCollectionFactory */
$ruleCollectionFactory = $objectManager->get(CollectionFactory::class);
$ruleCollection = $ruleCollectionFactory->create();
$ruleCollection->addFieldToFilter('name', ['eq' => 'Rule apply as fixed amount. Not logged user.']);
$ruleCollection->setPageSize(1);
/** @var Rule $rule */
$rule = $ruleCollection->getFirstItem();
if ($rule->getId()) {
    $ruleRepository->delete($rule);
}
$indexBuilder->reindexFull();
