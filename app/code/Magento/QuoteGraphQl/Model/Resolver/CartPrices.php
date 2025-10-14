<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\QuoteGraphQl\Model\Cart\TotalsCollector;
use Magento\Store\Model\ScopeInterface;

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
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param TotalsCollector $totalsCollector
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        TotalsCollector $totalsCollector,
        ?ScopeConfigInterface $scopeConfig = null
    ) {
        $this->totalsCollector = $totalsCollector;
        $this->scopeConfig = $scopeConfig ??  ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
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

        $appliedTaxes = $this->getAppliedTaxes($cartTotals, $currency);
        $grandTotal = $cartTotals->getGrandTotal();

        $totalAppliedTaxes = 0;
        foreach ($appliedTaxes as $appliedTax) {
            $totalAppliedTaxes += $appliedTax['amount']['value'];
        }
        $grandTotalExclTax = $grandTotal - $totalAppliedTaxes;

        return [
            'grand_total' => ['value' => $grandTotal, 'currency' => $currency],
            'grand_total_excluding_tax' => ['value' => $grandTotalExclTax, 'currency' => $currency],
            'subtotal_including_tax' => ['value' => $cartTotals->getSubtotalInclTax(), 'currency' => $currency],
            'subtotal_excluding_tax' => ['value' => $cartTotals->getSubtotal(), 'currency' => $currency],
            'subtotal_with_discount_excluding_tax' => [
                'value' => $this->getSubtotalWithDiscountExcludingTax($cartTotals),
                'currency' => $currency
            ],
            'applied_taxes' => $appliedTaxes,
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

        $rates = [];

        foreach ($appliedTaxes as $appliedTax) {
            $totalPercentage =  $appliedTax['percent'];
            foreach ($appliedTax['rates'] as $appliedTaxRate) {
                $rateTitle = $appliedTaxRate['title'];
                if (!array_key_exists($rateTitle, $rates)) {
                    $rates[$rateTitle] = 0.0;
                }
                $percentage = $appliedTaxRate['percent'];
                $taxValue = ($percentage / $totalPercentage) * $appliedTax['amount'];
                $rates[$rateTitle] += round((float) $taxValue, 2);
            }
        }

        foreach ($rates as $title => $amount) {
            $appliedTaxesData[] = [
                'label' => $title,
                'amount' => ['value' => $amount, 'currency' => $currency]
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
            'label' => $total->getDiscountDescription() !== null ? explode(', ', $total->getDiscountDescription()) : [],
            'amount' => ['value' => $total->getDiscountAmount(), 'currency' => $currency]
        ];
    }

    /**
     * Get Subtotal with discount excluding tax.
     *
     * @param Total $cartTotals
     * @return float
     */
    private function getSubtotalWithDiscountExcludingTax(Total $cartTotals): float
    {
        $discountIncludeTax = $this->scopeConfig->getValue(
            'tax/calculation/discount_tax',
            ScopeInterface::SCOPE_STORE
        ) ?? 0;
        $discountExclTax = $discountIncludeTax ?
            $cartTotals->getDiscountAmount() + $cartTotals->getDiscountTaxCompensationAmount() :
            $cartTotals->getDiscountAmount();

        return $cartTotals->getSubtotal() +  $discountExclTax;
    }
}
