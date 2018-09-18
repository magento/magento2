<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\BulkConfigurationUnassign;

class BulkUnassignConfiguration implements ObserverInterface
{
    /**
     * @var BulkConfigurationUnassign
     */
    private $bulkConfigurationUnassign;

    /**
     * @param BulkConfigurationUnassign $bulkConfigurationUnassign
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkConfigurationUnassign $bulkConfigurationUnassign
    ) {
        $this->bulkConfigurationUnassign = $bulkConfigurationUnassign;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $operation = $observer->getEvent()->getData('operation');
        $this->bulkConfigurationUnassign->execute(
            $operation->getSkus(),
            $operation->getSourceCodes()
        );
    }
}
