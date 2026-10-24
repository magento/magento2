<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\QuoteFactory;

class GetMaskedQuoteIdByReservedOrderId
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
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @param QuoteFactory $quoteFactory
     * @param QuoteResource $quoteResource
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedId
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteResource $quoteResource,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedId
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
        $this->quoteIdToMaskedId = $quoteIdToMaskedId;
    }

    /**
     * Get masked quote id by reserved order id
     *
     * @param string $reservedOrderId
     * @param bool $shouldCollectTotals
     * @return string
     * @throws NoSuchEntityException
     */
    public function execute(string $reservedOrderId, bool $shouldCollectTotals = false): string
    {
        $quote = $this->quoteFactory->create();
        $quote->setSharedStoreIds(['*']);
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');

        // If dataprovider is used, we need to collect totals manually and save quote
        if ($shouldCollectTotals) {
            $this->quoteResource->save($quote->collectTotals());
        }

        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }
}
