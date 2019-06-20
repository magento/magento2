<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Get cart for user
     *
     * @param string $cartHash
     * @param int|null $customerId
     * @return Quote
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     */
    public function execute(string $cartHash, ?int $customerId): Quote
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
            throw new GraphQlNoSuchEntityException(
                __('Current user does not have an active cart.')
            );
        }

        if ((int)$cart->getStoreId() !== (int)$this->storeManager->getStore()->getId()) {
            throw new GraphQlNoSuchEntityException(
                __(
                    'Wrong store code specified for cart "%masked_cart_id"',
                    ['masked_cart_id' => $cartHash]
                )
            );
        }

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
}
