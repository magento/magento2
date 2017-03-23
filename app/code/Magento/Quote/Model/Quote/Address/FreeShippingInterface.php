<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

interface FreeShippingInterface
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $items
     * @return bool
     */
    public function isFreeShipping(\Magento\Quote\Model\Quote $quote, $items);
}
