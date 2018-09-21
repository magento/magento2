<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Service for checking that the shopping cart operations are allowed for current user
 */
class IsCartMutationAllowedForCurrentUser
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param UserContextInterface $userContext
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        UserContextInterface $userContext,
        CartRepositoryInterface $cartRepository
    ) {
        $this->userContext = $userContext;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Check that the shopping cart operations are allowed for current user
     *
     * @param int $quoteId
     * @return bool
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(int $quoteId): bool
    {
        try {
            $quote = $this->cartRepository->get($quoteId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }

        $customerId = $quote->getCustomerId();

        /* Guest cart, allow operations */
        if (!$customerId) {
            return true;
        }

        /* If the quote belongs to the current customer allow operations */
        return $customerId == $this->userContext->getUserId();
    }
}
