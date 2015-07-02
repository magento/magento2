<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

/**
 * Shipping method management interface for guest carts.
 * @api
 */
interface GuestShippingMethodManagementInterface
{
    /**
     * Set the carrier and shipping methods codes for a specified cart.
     *
     * @param string $cartId The shopping cart ID.
     * @param string $carrierCode The carrier code.
     * @param string $methodCode The shipping method code.
     * @return bool
     * @throws \Magento\Framework\Exception\InputException The shipping method is not valid for an empty cart.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The shipping method could not be saved.
     * @throws \Magento\Framework\Exception\StateException The billing or shipping address is not set.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart contains only virtual products
     * so the shipping method does not apply.
     */
    public function set($cartId, $carrierCode, $methodCode);

    /**
     * Return selected shipping method for a specified quote.
     *
     * @param string $cartId The shopping cart ID.
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface Shipping method.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified shopping cart does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is not set.
     */
    public function get($cartId);

    /**
     * List applicable shipping methods for a specified quote.
     *
     * @param string $cartId The shopping cart ID.
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified quote does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is not set.
     */
    public function getList($cartId);

    /**
     * Estimate shipping
     *
     * @param string $cartId The shopping cart ID.
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address The estimate address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     */
    public function estimateByAddress($cartId, \Magento\Quote\Api\Data\EstimateAddressInterface $address);
}
