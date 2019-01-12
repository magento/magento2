<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

/**
 * Interface PaymentMethodManagementInterface
 * @api
 * @since 100.0.2
 */
interface PaymentMethodManagementInterface
{
    /**
     * Adds a specified payment method to a specified shopping cart.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Quote\Api\Data\PaymentInterface $method The payment method.
     * @return string redirect url or error message.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException The billing or shipping address
     * is not set, or the specified payment method is not available.
     */
    public function set($cartId, \Magento\Quote\Api\Data\PaymentInterface $method);

    /**
     * Returns the payment method for a specified shopping cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\PaymentInterface  Payment method object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get($cartId);

    /**
     * Lists available payment methods for a specified shopping cart.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.  See https://devdocs.magento.com/codelinks/attributes.html#PaymentMethodManagementInterface to
     * determine which call to use to get detailed information about all attributes for an object.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[] Array of payment methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getList($cartId);
}
