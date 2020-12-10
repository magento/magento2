<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftCardAccountGraphQl\Model\Money;

use Magento\Framework\Exception\LocalizedException;
use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Store\Model\Store;

/**
 * Utility Class for formatting
 */
class Formatter
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * Formatter constructor.
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(PriceCurrency $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }
    /**
     * Convert amount value into a Money type array
     *
     * @param float|string $amount
     * @param Store $store
     * @throws LocalizedException
     * @return array
     */
    public function formatAmountAsMoney($amount, Store $store): array
    {
        /** @var Store $store */
        $currentCurrency = $store->getCurrentCurrency();
        $baseCurrency = $store->getBaseCurrency();

        $convertedBalance = $baseCurrency->convert($amount, $currentCurrency);

        return [
            'value' => $convertedBalance,
            'currency' => $currentCurrency->getCode(),
            'formatted' => $this->priceCurrency->format($convertedBalance,false,null,null,$currentCurrency->getCode())
        ];
    }
}
