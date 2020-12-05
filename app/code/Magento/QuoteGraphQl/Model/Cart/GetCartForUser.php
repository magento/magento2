<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Get cart
 */
class GetCartForUser
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
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        StoreRepositoryInterface $storeRepository = null
    ) {
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
        $this->storeRepository = $storeRepository ?: ObjectManager::getInstance()->get(StoreRepositoryInterface::class);
    }

    /**
     * Get cart for user
     *
     * @param string $cartHash
     * @param int|null $customerId
     * @param int $storeId
     * @return Quote
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     */
    public function execute(string $cartHash, ?int $customerId, int $storeId): Quote
    {
        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($cartHash);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%masked_cart_id"', ['masked_cart_id' => $cartHash])
            );
        }

        try {
            /** @var Quote $cart */
            $cart = $this->cartRepository->get($cartId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%masked_cart_id"', ['masked_cart_id' => $cartHash])
            );
        }

        if (false === (bool)$cart->getIsActive()) {
            throw new GraphQlNoSuchEntityException(__('The cart isn\'t active.'));
        }

        $this->updateCartCurrency($cart, $storeId);

        $cartCustomerId = (int)$cart->getCustomerId();

        /* Guest cart, allow operations */
        if (0 === $cartCustomerId && (null === $customerId || 0 === $customerId)) {
            return $cart;
        }

        if ($cartCustomerId !== $customerId) {
            throw new GraphQlAuthorizationException(
                __(
                    'The current user cannot perform operations on cart "%masked_cart_id"',
                    ['masked_cart_id' => $cartHash]
                )
            );
        }
        return $cart;
    }

    /**
     * Sets cart currency based on specified store.
     *
     * @param Quote $cart
     * @param int $storeId
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     */
    private function updateCartCurrency(Quote $cart, int $storeId)
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
            return;
        }
        $this->cartRepository->save($cart);
    }
}
