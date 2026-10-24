<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

// refresh report statistics
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Tax\Model\ResourceModel\Report\Tax $reportResource */
$reportResource = $objectManager->create(\Magento\Tax\Model\ResourceModel\Report\Tax::class);
$reportResource->beginTransaction();
// prevent table truncation by incrementing the transaction nesting level counter
try {
    $reportResource->aggregate();
    $reportResource->commit();
} catch (\Exception $e) {
    $reportResource->rollBack();
    throw $e;
}
