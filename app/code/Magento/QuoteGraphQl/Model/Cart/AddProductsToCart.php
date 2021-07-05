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
use Magento\Framework\App\CacheInterface;

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
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AddSimpleProductToCart $addProductToCart
     * @param CacheInterface $cache
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddSimpleProductToCart $addProductToCart,
        CacheInterface $cache
    ) {
        $this->cartRepository = $cartRepository;
        $this->addProductToCart = $addProductToCart;
        $this->cache = $cache;
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
        $ck = 'cart_processing_mutex_' . $cart->getId();
        while ($this->cache->load($ck) === '1') {
            // wait till other process working with the same cart complete
            usleep(rand (300, 600));
        }
        $this->cache->save('1', $ck, [], 1);
        foreach ($cartItems as $cartItemData) {
            $this->addProductToCart->execute($cart, $cartItemData);
        }
        $this->cartRepository->save($cart);
        $this->cache->remove($ck);
    }
}
