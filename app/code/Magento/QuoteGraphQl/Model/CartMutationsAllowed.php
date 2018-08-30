<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * {@inheritDoc}
 */
class CartMutationsAllowed implements CartMutationsAllowedInterface
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
     * {@inheritDoc}
     */
    public function execute(int $quoteId): bool
    {
        try {
            $quote = $this->cartRepository->get($quoteId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }

        $customerId = $quote->getCustomerId();

        if (!$customerId) {
            return true;
        }

        if ($customerId == $this->userContext->getUserId()) {
            return true;
        }

        return false;
    }
}
