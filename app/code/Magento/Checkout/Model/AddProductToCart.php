<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteMutexInterface;

class AddProductToCart
{
    /**
     * @param QuoteMutexInterface $quoteMutex
     * @param CartRepositoryInterfaceFactory $quoteRepositoryFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        private readonly QuoteMutexInterface $quoteMutex,
        private readonly CartRepositoryInterfaceFactory $quoteRepositoryFactory,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * Add product to cart
     *
     * @param Cart $cart
     * @param Product $product
     * @param array $buyRequest Buy request info
     * @param array $related Product IDs to add to the cart along with the main product
     * @return bool
     * @throws \Throwable
     */
    public function execute(Cart $cart, Product $product, array $buyRequest = [], array $related = []): bool
    {
        if (!$cart->getQuote()->getId()) {
            return $this->add($cart, $product, $buyRequest, $related);
        }

        return $this->quoteMutex->execute(
            [(int) $cart->getQuote()->getId()],
            function (array $quotes = []) use ($cart, $product, $buyRequest, $related) {
                $reload = true;
                // check if the mutex provided the quote
                if (!empty($quotes)) {
                    // check if the quote was updated since the last load
                    // if not, we can use the quote in memory to avoid full reload which is expensive and unnecessary
                    $lastUpdatedAt = $cart->getQuote()->getUpdatedAt()
                        ?: $cart->getQuote()->getOrigData(CartInterface::KEY_UPDATED_AT);
                    $quote = current($quotes);
                    $updatedAt = $quote->getUpdatedAt();
                    $reload = $updatedAt
                        && $lastUpdatedAt
                        && $this->dateTime->timestamp($updatedAt) > $this->dateTime->timestamp($lastUpdatedAt);
                }
                if ($reload) {
                    // bypass repository cache by creating a new repository instead of using the shared repository
                    $quote = $this->quoteRepositoryFactory->create()->getActive($cart->getQuote()->getId());
                    $cart->setQuote($quote);
                    $cart->getCheckoutSession()->replaceQuote($quote);
                }
                return $this->add($cart, $product, $buyRequest, $related);
            },
        );
    }

    /**
     * Add product to cart
     *
     * @param Cart $cart
     * @param Product $product
     * @param array $buyRequest
     * @param array $related
     * @return bool
     */
    private function add(Cart $cart, Product $product, array $buyRequest, array $related = []): bool
    {
        $cart->addProduct($product, $buyRequest);
        if (!empty($related)) {
            $cart->addProductsByIds($related);
        }
        $cart->save();
        return true;
    }
}
