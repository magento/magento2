<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Update currency
 */
class UpdateCartCurrency
{
    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private StoreRepositoryInterface $storeRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Sets cart currency based on specified store.
     *
     * @param Quote $cart
     * @param int $storeId
     * @return Quote
     * @throws GraphQlInputException|NoSuchEntityException|LocalizedException
     */
    public function execute(Quote $cart, int $storeId): Quote
    {
        $cartStore = $this->storeRepository->getById($cart->getStoreId());
        $currentCartCurrencyCode = $cartStore->getCurrentCurrency()->getCode();
        if ((int)$cart->getStoreId() !== $storeId) {
            $newStore = $this->storeRepository->getById($storeId);
            if ($cartStore->getWebsite() !== $newStore->getWebsite()) {
                throw new GraphQlInputException(
                    __('Can\'t assign cart to store in different website.')
                );
            }
            $cart->setStoreId($storeId);
            $cart->setStoreCurrencyCode($newStore->getCurrentCurrency());
            $cart->setQuoteCurrencyCode($newStore->getCurrentCurrency());
        } elseif ($cart->getQuoteCurrencyCode() !== $currentCartCurrencyCode) {
            $cart->setQuoteCurrencyCode($cartStore->getCurrentCurrency());
        } else {
            return $cart;
        }
        $this->cartRepository->save($cart);
        return $this->cartRepository->get($cart->getId());
    }
}
