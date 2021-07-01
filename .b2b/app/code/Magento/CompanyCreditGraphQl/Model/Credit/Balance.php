<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompanyCreditGraphQl\Model\Credit;

use Magento\CompanyCredit\Model\HistoryInterface;
use Magento\NegotiableQuote\Model\PriceCurrency;

/**
 * Credit history balance
 */
class Balance
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * Balance constructor.
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(PriceCurrency $priceCurrency){
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get operation balance data
     *
     * @param HistoryInterface $creditOperation
     * @return array[]
     */
    public function getBalance(HistoryInterface $creditOperation): array
    {
        return [
            'outstanding_balance' => $this->formatData(
                $creditOperation->getCurrencyOperation(),
                (float)$creditOperation->getBalance(),
                $this->priceCurrency->format((float)$creditOperation->getBalance(),false,null,null,$creditOperation->getCurrencyOperation())
            ),
            'available_credit' => $this->formatData(
                $creditOperation->getCurrencyOperation(),
                (float)$creditOperation->getAvailableLimit(),
                $this->priceCurrency->format((float)$creditOperation->getAvailableLimit(),false,null,null,$creditOperation->getCurrencyOperation())
            ),
            'credit_limit' => $this->formatData(
                $creditOperation->getCurrencyOperation(),
                (float)$creditOperation->getCreditLimit(),
                $this->priceCurrency->format((float)$creditOperation->getCreditLimit(),false,null,null,$creditOperation->getCurrencyOperation())
            )
        ];
    }

    /**
     * Format credit response data
     *
     * @param string $currency
     * @param float $value
     * @param string $formatted
     * @return array
     */
    public function formatData(string $currency, float $value, string $formatted): array
    {
        return [
            'currency' => $currency,
            'value' => $value,
            'formatted' => $formatted
        ];
    }
}
