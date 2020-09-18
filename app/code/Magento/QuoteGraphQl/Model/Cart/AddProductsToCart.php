<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Message\MessageInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Adding products to cart using GraphQL
 */
class AddProductsToCart
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var AddSimpleProductToCart
     */
    private $addProductToCart;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AddSimpleProductToCart $addProductToCart
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddSimpleProductToCart $addProductToCart
    ) {
        $this->cartRepository = $cartRepository;
        $this->addProductToCart = $addProductToCart;
    }

    /**
     * Add products to cart
     *
     * @param Quote $cart
     * @param array $cartItems
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException
     */
    public function execute(Quote $cart, array $cartItems): void
    {
        foreach ($cartItems as $cartItemData) {
            $this->addProductToCart->execute($cart, $cartItemData);
        }

        $this->cartRepository->save($cart);
    }
}
