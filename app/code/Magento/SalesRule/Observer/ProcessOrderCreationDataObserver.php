<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class for process order for resetting shipping flag.
 */
class ProcessOrderCreationDataObserver implements ObserverInterface
{
    /**
     * Checking shipping method and resetting it if needed.
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrderCreateModel();
        $request = $observer->getEvent()->getRequest();
        if (array_key_exists('order', $request)) {
            $quote = $order->getQuote();
            $isVirtualQuote = $quote->isVirtual();
            $quoteShippingMethod = $observer->getEvent()->getShippingMethod();
            $checkIfCouponExists = array_key_exists('coupon', $request['order']);
            if (!$isVirtualQuote && !empty($quoteShippingMethod) && $checkIfCouponExists) {
                    $shippingAddress = $quote->getShippingAddress();
                    $shippingAddress->setShippingMethod($quoteShippingMethod);
            }
        }
        return $this;
    }
}
