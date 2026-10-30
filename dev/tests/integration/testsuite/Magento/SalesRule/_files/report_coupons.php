<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

// refresh report statistics
/** @var \Magento\SalesRule\Model\ResourceModel\Report\Rule $reportResource */
$reportResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\SalesRule\Model\ResourceModel\Report\Rule::class
);
$reportResource->beginTransaction();
// prevent table truncation by incrementing the transaction nesting level counter
try {
    $reportResource->aggregate();
    $reportResource->commit();
} catch (\Exception $e) {
    $reportResource->rollBack();
    throw $e;
}
