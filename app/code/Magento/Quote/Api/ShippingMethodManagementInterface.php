<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;

/**
 * Interface ShippingMethodManagementInterface
 * @api
 * @since 100.0.2
 */
interface ShippingMethodManagementInterface
{
    /**
     * Estimate shipping
     *
     * @param int $cartId The shopping cart ID.
     * @param EstimateAddressInterface $address The estimate address
     * @return ShippingMethodInterface[] An array of shipping methods.
     * @deprecated 100.0.7
     */
    public function estimateByAddress($cartId, EstimateAddressInterface $address);

    /**
     * Estimate shipping
     *
     * @param int $cartId The shopping cart ID.
     * @param int $addressId The estimate address id
     * @return ShippingMethodInterface[] An array of shipping methods.
     */
    public function estimateByAddressId($cartId, $addressId);

    /**
     * Lists applicable shipping methods for a specified quote.
     *
     * @param int $cartId The shopping cart ID.
     * @return ShippingMethodInterface[] An array of shipping methods.
     * @throws NoSuchEntityException The specified quote does not exist.
     * @throws StateException The shipping address is missing.
     */
    public function getList($cartId);

    /**
     * Sets the carrier and shipping methods codes for a specified cart.
     *
     * @param int $cartId The shopping cart ID.
     * @param string $carrierCode The carrier code.
     * @param string $methodCode The shipping method code.
     * @return bool
     * @throws InputException The shipping method is not valid for an empty cart.
     * @throws CouldNotSaveException The shipping method could not be saved.
     * @throws StateException The billing or shipping address is missing.
     * @throws NoSuchEntityException The specified cart contains only virtual products
     * so the shipping method does not apply.
     */
    public function set($cartId, $carrierCode, $methodCode);

    /**
     * Returns selected shipping method for a specified quote.
     *
     * @param int $cartId The shopping cart ID.
     * @return ShippingMethodInterface Shipping method.
     * @throws NoSuchEntityException The specified shopping cart does not exist.
     * @throws StateException The shipping address is missing.
     */
    public function get($cartId);
}
