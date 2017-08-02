<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

/**
 * @api
 * @since 2.0.0
 */
interface FreeShippingInterface
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $items
     * @return bool
     * @since 2.0.0
     */
    public function isFreeShipping(\Magento\Quote\Model\Quote $quote, $items);
}
