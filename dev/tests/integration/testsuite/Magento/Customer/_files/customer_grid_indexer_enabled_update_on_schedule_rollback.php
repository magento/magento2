<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
$indexerRegistry = $objectManager->create(\Magento\Framework\Indexer\IndexerRegistry::class);
$indexer = $indexerRegistry->get(\Magento\Customer\Model\Customer::CUSTOMER_GRID_INDEXER_ID);
$indexer->setScheduled(false);

// Because integration tests don't run cron, we need to manually ensure the
// index isn't still invalid as that will cause asumptions in the tests to no
// longer hold.
if ($indexer->isInvalid()) {
    $indexer->reindexAll();
}
