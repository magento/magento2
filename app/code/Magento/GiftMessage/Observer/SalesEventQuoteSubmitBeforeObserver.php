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
class SalesEventQuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * Set gift messages to order from quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getEvent()->getOrder()->setGiftMessageId($observer->getEvent()->getQuote()->getGiftMessageId());

        return $this;
    }
}
