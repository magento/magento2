<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// refresh report statistics
/** @var \Magento\Sales\Model\ResourceModel\Report\Refunded $reportResource */
$reportResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Sales\Model\ResourceModel\Report\Refunded::class
);
$reportResource->beginTransaction();
// prevent table truncation by incrementing the transaction nesting level counter
try {
    $reportResource->aggregate();
    $reportResource->commit();
} catch (\Magento\Framework\Exception\AfterCommitException $e) {
    throw $e->getPrevious();
} catch (\Exception $e) {
    $reportResource->rollBack();
    throw $e;
}
