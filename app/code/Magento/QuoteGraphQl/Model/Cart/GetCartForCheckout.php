<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;

/**
 * Get cart
 */
class GetCartForCheckout
{
    /**
     * @var CheckCartCheckoutAllowance
     */
    private CheckCartCheckoutAllowance $checkoutAllowance;

    /**
     * @var GetCartForUser
     */
    private GetCartForUser $getCartForUser;

    /**
     * @param CheckCartCheckoutAllowance $checkoutAllowance
     * @param GetCartForUser $getCartForUser
     */
    public function __construct(
        CheckCartCheckoutAllowance $checkoutAllowance,
        GetCartForUser $getCartForUser
    ) {
        $this->checkoutAllowance = $checkoutAllowance;
        $this->getCartForUser = $getCartForUser;
    }

    /**
     * Gets the cart for the user validated and configured for guest checkout if applicable
     *
     * @param string $cartHash
     * @param int|null $customerId
     * @param int $storeId
     * @return Quote
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(string $cartHash, ?int $customerId, int $storeId): Quote
    {
        try {
            $cart = $this->getCartForUser->execute($cartHash, $customerId, $storeId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        $this->checkoutAllowance->execute($cart);

        if (null === $customerId || 0 === $customerId) {
            if (!$cart->getCustomerEmail()) {
                throw new GraphQlInputException(__("Guest email for cart is missing."));
            }
            $cart->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
        }

        return $cart;
    }
}
