<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

/**
 * Interface \Magento\Quote\Model\ShippingMethodManagementInterface
 *
 */
interface ShippingMethodManagementInterface
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
     * @throws \Magento\Framework\Exception\StateException The billing or shipping address is missing.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart contains only virtual products
     * so the shipping method does not apply.
     */
    public function set($cartId, $carrierCode, $methodCode);

    /**
     * Returns selected shipping method for a specified quote.
     *
     * @param int $cartId The shopping cart ID.
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface Shipping method.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified shopping cart does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is missing.
     */
    public function get($cartId);

    /**
     * Estimate shipping
     *
     * @param int $cartId The shopping cart ID.
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address The estimate address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @deprecated 100.0.7
     */
    public function estimateByAddress($cartId, \Magento\Quote\Api\Data\EstimateAddressInterface $address);

    /**
     * Estimate shipping
     *
     * @param int $cartId The shopping cart ID.
     * @param int $addressId The estimate address id
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     */
    public function estimateByAddressId($cartId, $addressId);

    /**
     * Lists applicable shipping methods for a specified quote.
     *
     * @param int $cartId The shopping cart ID.
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified quote does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is missing.
     */
    public function getList($cartId);
}
