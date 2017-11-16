<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\QuoteManagement;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Purchase products from quote.
 *
 * @api May be used for pluginization.
 */
class Purchase
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CartManagementInterface
     */
    private $quoteManagement;

    /**
     * Purchase constructor.
     * @param CartRepositoryInterface $quoteRepository
     * @param CartManagementInterface $quoteManagement
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CartManagementInterface $quoteManagement
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
    }

    /**
     * Summarize quote and place order.
     *
     * @param Quote $quote
     * @return int Order id
     * @throws LocalizedException if order can not be placed for a quote.
     */
    public function purchase(Quote $quote): int
    {
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $orderId = $this->quoteManagement->placeOrder($quote->getId());
        return $orderId;
    }
}
