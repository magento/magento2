<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\QuoteFactory;
use \Magento\Quote\Model\Quote;

/**
 * Get quote model id by reserved order id
 */
class GetQuoteByReservedOrderId
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
     * Get masked quote id by reserved order id
     *
     * @param string $reservedOrderId
     * @return Quote
     * @throws NoSuchEntityException
     */
    public function execute(string $reservedOrderId): Quote
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');

        return $quote;
    }
}
