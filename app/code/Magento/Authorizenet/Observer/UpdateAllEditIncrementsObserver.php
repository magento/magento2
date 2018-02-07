<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class UpdateAllEditIncrementsObserver implements ObserverInterface
{
    /**
     *
     * @var \Magento\Authorizenet\Helper\Data
     */
    protected $authorizenetData;

    /**
     * @param \Magento\Authorizenet\Helper\Data $authorizenetData
     */
    public function __construct(
        \Magento\Authorizenet\Helper\Data $authorizenetData
    ) {
        $this->authorizenetData = $authorizenetData;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getData('order');
        $this->authorizenetData->updateOrderEditIncrements($order);

        return $this;
    }
}
