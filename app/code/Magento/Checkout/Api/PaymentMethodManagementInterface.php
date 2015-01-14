<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api;

interface PaymentMethodManagementInterface
{
    /**
     * Adds a specified payment method to a specified shopping cart.
     *
     * @param \Magento\Checkout\Api\Data\PaymentInterface $method The payment method.
     * @param int $cartId The cart ID.
     * @return int Payment method ID.
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException The billing or shipping address is not set, or the specified payment method is not available.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @see \Magento\Checkout\Service\V1\PaymentMethod\WriteServiceInterface::set
     */
    public function set(\Magento\Checkout\Api\Data\PaymentInterface $method, $cartId);

    /**
     * Returns the payment method for a specified shopping cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Api\Data\PaymentInterface  Payment method object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @see \Magento\Checkout\Service\V1\PaymentMethod\ReadServiceInterface::getPayment
     */
    public function get($cartId);

    /**
     * Lists available payment methods for a specified shopping cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Api\Data\PaymentMethodInterface[] Array of payment methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @see \Magento\Checkout\Service\V1\PaymentMethod\ReadServiceInterface::getList
     */
    public function getList($cartId);
}
