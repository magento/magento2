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
use Magento\Framework\Message\AbstractMessage;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Authorization\IsCartMutationAllowedForCurrentUser;

/**
 * Add products to cart
 */
class AddProductsToCart
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var IsCartMutationAllowedForCurrentUser
     */
    private $isCartMutationAllowedForCurrentUser;

    /**
     * @var AddSimpleProductToCart
     */
    private $addProductToCart;

    /**
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param IsCartMutationAllowedForCurrentUser $isCartMutationAllowedForCurrentUser
     * @param AddSimpleProductToCart $addProductToCart
     */
    public function __construct(
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        IsCartMutationAllowedForCurrentUser $isCartMutationAllowedForCurrentUser,
        AddSimpleProductToCart $addProductToCart
    ) {
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
        $this->isCartMutationAllowedForCurrentUser = $isCartMutationAllowedForCurrentUser;
        $this->addProductToCart = $addProductToCart;
    }

    /**
     * Add products to cart
     *
     * @param string $cartHash
     * @param array $cartItems
     * @return Quote
     * @throws GraphQlInputException
     */
    public function execute(string $cartHash, array $cartItems): Quote
    {
        $cart = $this->getCart($cartHash);

        foreach ($cartItems as $cartItemData) {
            $this->addProductToCart->execute($cart, $cartItemData);
        }

        if ($cart->getData('has_error')) {
            throw new GraphQlInputException(
                __('Shopping cart error: %message', ['message' => $this->getCartErrors($cart)])
            );
        }

        $this->cartRepository->save($cart);
        return $cart;
    }

    /**
     * Get cart
     *
     * @param string $cartHash
     * @return Quote
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlAuthorizationException
     */
    private function getCart(string $cartHash): Quote
    {
        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($cartHash);
            $cart = $this->cartRepository->get($cartId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%masked_cart_id"', ['masked_cart_id' => $cartHash])
            );
        }

        if (false === $this->isCartMutationAllowedForCurrentUser->execute($cartId)) {
            throw new GraphQlAuthorizationException(
                __(
                    'The current user cannot perform operations on cart "%masked_cart_id"',
                    ['masked_cart_id' => $cartHash]
                )
            );
        }

        /** @var Quote $cart */
        return $cart;
    }

    /**
     * Collecting cart errors
     *
     * @param Quote $cart
     * @return string
     */
    private function getCartErrors(Quote $cart): string
    {
        $errorMessages = [];

        /** @var AbstractMessage $error */
        foreach ($cart->getErrors() as $error) {
            $errorMessages[] = $error->getText();
        }

        return implode(PHP_EOL, $errorMessages);
    }
}
