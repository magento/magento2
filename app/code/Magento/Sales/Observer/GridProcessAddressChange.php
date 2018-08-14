<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\ResourceModel\GridPool;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class GridProcessAddressChange
 */
class GridProcessAddressChange implements ObserverInterface
{

    /**
     * @var GridPool
     */
    protected $gridPool;

    /**
     * @param GridPool $gridPool
     */
    public function __construct(
        GridPool $gridPool
    ) {
        $this->gridPool= $gridPool;
    }

    /**
     * Refresh addresses in grids according to performed changed
     * This is manual admin action, as result we perform this operation without delay
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $this->gridPool->refreshByOrderId($observer->getOrderId());
    }
}
