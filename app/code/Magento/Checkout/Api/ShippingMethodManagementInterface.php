<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Api;

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
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart contains only virtual products so the shipping method does not apply.
     * @throws \Magento\Framework\Exception\StateException The billing or shipping address is not set.
     * @see \Magento\Checkout\Service\V1\ShippingMethod\WriteServiceInterface::setMethod
     */
    public function set($cartId, $carrierCode, $methodCode);

    /**
     * Returns selected shipping method for a specified quote.
     *
     * @param int $cartId The shopping cart ID.
     * @return \Magento\Checkout\Api\Data\ShippingMethodInterface Shipping method.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified shopping cart does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is not set.
     * @see \Magento\Checkout\Service\V1\ShippingMethod\ReadServiceInterface::getMethod
     */
    public function get($cartId);

    /**
     * Lists applicable shipping methods for a specified quote.
     *
     * @param int $cartId The shopping cart ID.
     * @return \Magento\Checkout\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified quote does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is not set.
     * @see \Magento\Checkout\Service\V1\ShippingMethod\ReadServiceInterface::getList
     */
    public function getList($cartId);
}
