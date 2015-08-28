<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Observer;

class QuoteCollectTotalsBefore
{
    /**
     * Reset extra tax amounts on quote addresses before recollecting totals
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function invoke(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getEvent()->getQuote();
        foreach ($quote->getAllAddresses() as $address) {
            $address->setExtraTaxAmount(0);
            $address->setBaseExtraTaxAmount(0);
        }
        return $this;
    }
}
