<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\QuoteFactory;

/**
 * Get quote shipping address id by reserved order id
 */
class GetQuoteShippingAddressIdByReservedQuoteId
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @param QuoteFactory $quoteFactory
     * @param QuoteResource $quoteResource
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteResource $quoteResource
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
    }

    /**
     * Get quote shipping address id by reserved order id
     *
     * @param string $reservedOrderId
     * @return int
     */
    public function execute(string $reservedOrderId): int
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');

        return (int)$quote->getShippingAddress()->getId();
    }
}
