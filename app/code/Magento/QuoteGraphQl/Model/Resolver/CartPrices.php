<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\TotalsCollector;

/**
 * @inheritdoc
 */
class CartPrices implements ResolverInterface
{
    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param TotalsCollector $totalsCollector
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        TotalsCollector $totalsCollector,
        PriceCurrency $priceCurrency
    ) {
        $this->totalsCollector = $totalsCollector;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Quote $quote */
        $quote = $value['model'];
        /**
         * To calculate a right discount value
         * before calculate totals
         * need to reset Cart Fixed Rules in the quote
         */
        $quote->setCartFixedRules([]);
        $cartTotals = $this->totalsCollector->collectQuoteTotals($quote);
        $currency = $quote->getQuoteCurrencyCode();

        return [
            'grand_total' => ['value' => $cartTotals->getGrandTotal(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($cartTotals->getGrandTotal(),false,null,null,$currency)],
            'subtotal_including_tax' => ['value' => $cartTotals->getSubtotalInclTax(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($cartTotals->getSubtotalInclTax(),false,null,null,$currency)],
            'subtotal_excluding_tax' => ['value' => $cartTotals->getSubtotal(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($cartTotals->getSubtotal(),false,null,null,$currency)],
            'subtotal_with_discount_excluding_tax' => [
                'value' => $cartTotals->getSubtotalWithDiscount(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($cartTotals->getSubtotalWithDiscount(),false,null,null,$currency)
            ],
            'applied_taxes' => $this->getAppliedTaxes($cartTotals, $currency),
            'discount' => $this->getDiscount($cartTotals, $currency),
            'model' => $quote
        ];
    }

    /**
     * Returns taxes applied to the current quote
     *
     * @param Total $total
     * @param string $currency
     * @return array
     */
    private function getAppliedTaxes(Total $total, string $currency): array
    {
        $appliedTaxesData = [];
        $appliedTaxes = $total->getAppliedTaxes();

        if (empty($appliedTaxes)) {
            return $appliedTaxesData;
        }

        foreach ($appliedTaxes as $appliedTax) {
            $appliedTaxesData[] = [
                'label' => $appliedTax['id'],
                'amount' => ['value' => $appliedTax['amount'], 'currency' => $currency, 'formatted' => $this->priceCurrency->format($appliedTax['amount'],false,null,null,$currency)]
            ];
        }
        return $appliedTaxesData;
    }

    /**
     * Returns information about an applied discount
     *
     * @param Total $total
     * @param string $currency
     * @return array|null
     */
    private function getDiscount(Total $total, string $currency)
    {
        if ($total->getDiscountAmount() === 0) {
            return null;
        }
        return [
            'label' => explode(', ', $total->getDiscountDescription()),
            'amount' => ['value' => $total->getDiscountAmount(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($total->getDiscountAmount(),false,null,null,$currency)]
        ];
    }
}
