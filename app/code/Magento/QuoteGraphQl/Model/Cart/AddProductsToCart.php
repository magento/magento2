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
 * Add products to cart
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
     * @return \Magento\Framework\GraphQl\Exception\GraphQlCartInputException
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException
     */
    public function execute(Quote $cart, array $cartItems): \Magento\Framework\GraphQl\Exception\GraphQlCartInputException
    {
        foreach ($cartItems as $cartItemData) {
            $this->addProductToCart->execute($cart, $cartItemData);
        }

        if ($cart->getData('has_error')) {
            $e = new \Magento\Framework\GraphQl\Exception\GraphQlCartInputException(__('Shopping cart errors'));
            $errors = $cart->getErrors();
            foreach ($errors as $error) {
                /** @var MessageInterface $error */
                $e->addError(new GraphQlInputException(__($error->getText())));
            }
            $e->addData($cartItems);

            throw $e;
        }

        $this->cartRepository->save($cart);
    }
}
