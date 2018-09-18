<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\BulkConfigurationTransfer;

class BulkTransferConfiguration implements ObserverInterface
{
    /**
     * @var BulkConfigurationTransfer
     */
    private $bulkConfigurationTransfer;

    /**
     * @param BulkConfigurationTransfer $bulkConfigurationTransfer
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkConfigurationTransfer $bulkConfigurationTransfer
    ) {
        $this->bulkConfigurationTransfer = $bulkConfigurationTransfer;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $operation = $observer->getEvent()->getData('operation');

        $this->bulkConfigurationTransfer->execute(
            $operation->getSkus(),
            $operation->getOriginSource(),
            $operation->getDestinationSource(),
            $operation->getUnassignFromOrigin()
        );
    }
}
