<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\ShippingMethod;

/**
 * Interface to choose the shipping method for a cart address.
 */
interface WriteServiceInterface
{
    /**
     * Sets the carrier and shipping methods codes for a specified cart.
     *
     * @param int $cartId The shopping cart ID.
     * @param string $carrierCode The carrier code.
     * @param string $methodCode The shipping method code.
     * @return bool
     * @throws \Magento\Framework\Exception\InputException The shipping method is not valid for an empty cart.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The shipping method could not be saved.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart contains only virtual products so the shipping method does not apply.
     * @throws \Magento\Framework\Exception\StateException The billing or shipping address is not set.
     */
    public function setMethod($cartId, $carrierCode, $methodCode);
}
