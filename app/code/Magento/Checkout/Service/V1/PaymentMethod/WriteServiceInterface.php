<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Service\V1\PaymentMethod;

/**
 * Payment method write service interface.
 */
interface WriteServiceInterface
{
    /**
     * Adds a specified payment method to a specified shopping cart.
     *
     * @param \Magento\Checkout\Service\V1\Data\Cart\PaymentMethod $method The payment method.
     * @param int $cartId The cart ID.
     * @return int Payment method ID.
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException The billing or shipping address is not set, or the specified payment method is not available.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function set(\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod $method, $cartId);
}
