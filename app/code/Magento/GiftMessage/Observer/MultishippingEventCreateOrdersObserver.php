<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Gift Message Observer Model
 */
class MultishippingEventCreateOrdersObserver implements ObserverInterface
{
    /**
     * Set gift message to order from address in multiple addresses checkout.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getEvent()->getOrder()->setGiftMessageId($observer->getEvent()->getAddress()->getGiftMessageId());

        return $this;
    }
}
