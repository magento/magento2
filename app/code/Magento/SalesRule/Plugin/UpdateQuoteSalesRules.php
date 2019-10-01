<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Save quote if sales rules have been changed
 */
class UpdateQuoteSalesRules
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * SalesRuleChangeDetector constructor.
     *
     * @param CartRepositoryInterface|null $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Save quote if sales rules have been changed
     *
     * @param \Magento\Quote\Model\Cart\CartTotalRepository $subject
     * @param \Magento\Quote\Api\Data\TotalsInterface $result
     * @param int $cartId
     * @return \Magento\Quote\Api\Data\TotalsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Quote\Model\Cart\CartTotalRepository $subject,
        $result,
        $cartId
    ) {
        $quote = $this->quoteRepository->get($cartId);
        if ($this->isChanged($quote)) {
            $this->quoteRepository->save($quote);
        }
        return $result;
    }
    /**
     * Determine whether sales rules have been changed for provided quote
     *
     * @param CartInterface $quote
     * @return bool
     */
    private function isChanged(CartInterface $quote): bool
    {
        foreach ($quote->getItems() as $quoteItem) {
            if ($quoteItem->dataHasChangedFor(OrderItemInterface::APPLIED_RULE_IDS)) {
                return true;
            }
        }
        return false;
    }
}
