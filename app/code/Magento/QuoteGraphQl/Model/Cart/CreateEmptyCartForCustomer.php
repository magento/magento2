<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\Cart\CustomerCartResolver;

/**
 * Create empty cart for customer
 * Masked quote ID will be returned as a result
 */
class CreateEmptyCartForCustomer
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @var CustomerCartResolver
     */
    private $cartResolver;

    /**
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param CustomerCartResolver $cartResolver
     */
    public function __construct(
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        CustomerCartResolver $cartResolver
    ) {
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->cartResolver = $cartResolver;
    }

    /**
     * Create empty cart for customer
     *
     * @param int $customerId
     * @param string|null $predefinedMaskedQuoteId
     * @return string
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(int $customerId, string $predefinedMaskedQuoteId = null): string
    {
        $cart = $this->cartResolver->resolve($customerId, $predefinedMaskedQuoteId);
        $quoteId = (int) $cart->getId();

        return $this->quoteIdToMaskedQuoteId->execute($quoteId);
    }
}
