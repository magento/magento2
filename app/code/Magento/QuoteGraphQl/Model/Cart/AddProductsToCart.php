<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Lock\LockManagerInterface;

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
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AddSimpleProductToCart $addProductToCart
     * @param LockManagerInterface $lockManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddSimpleProductToCart $addProductToCart,
        LockManagerInterface $lockManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->addProductToCart = $addProductToCart;
        $this->lockManager = $lockManager;
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
        $lockName = 'cart_processing_lock_' . $cart->getId();
        while ($this->lockManager->isLocked($lockName)) {
            // wait till other process working with the same cart complete
            usleep(rand (300, 600));
        }
        $this->lockManager->lock($lockName);
        $this->refreshCartCache($cart);
        foreach ($cartItems as $cartItemData) {
            $this->addProductToCart->execute($cart, $cartItemData);
        }
        $this->cartRepository->save($cart);
        $this->lockManager->unlock($lockName);
    }

    /**
     * Refresh cart collection cache
     *
     * @param Quote $cart
     */
    private function refreshCartCache(Quote $cart) : void
    {
        $items = [];
        $collection = $cart->getItemsCollection(false);
        foreach ($collection as $item) {
            $items[] = $item;
        }
        $cart->setItems($items);
    }
}
