<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\BulkConfigurationAssign;

class BulkAssignSourceConfiguration implements ObserverInterface
{
    /**
     * @var BulkConfigurationAssign
     */
    private $bulkConfigurationAssign;

    /**
     * @param BulkConfigurationAssign $bulkConfigurationAssign
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkConfigurationAssign $bulkConfigurationAssign
    ) {
        $this->bulkConfigurationAssign = $bulkConfigurationAssign;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $operation = $observer->getEvent()->getData('operation');
        $this->bulkConfigurationAssign->execute(
            $operation->getSkus(),
            $operation->getSourceCodes()
        );
    }

}