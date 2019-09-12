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
     * @param TotalsCollector $totalsCollector
     */
    public function __construct(
        TotalsCollector $totalsCollector
    ) {
        $this->totalsCollector = $totalsCollector;
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
        $cartTotals = $this->totalsCollector->collectQuoteTotals($quote);
        $currency = $quote->getQuoteCurrencyCode();

        return [
            'grand_total' => ['value' => $cartTotals->getGrandTotal(), 'currency' => $currency],
            'subtotal_including_tax' => ['value' => $cartTotals->getSubtotalInclTax(), 'currency' => $currency],
            'subtotal_excluding_tax' => ['value' => $cartTotals->getSubtotal(), 'currency' => $currency],
            'subtotal_with_discount_excluding_tax' => [
                'value' => $cartTotals->getSubtotalWithDiscount(), 'currency' => $currency
            ],
            'applied_taxes' => $this->getAppliedTaxes($cartTotals, $currency),
            'discount' => $this->getDiscount($cartTotals, $currency),
            'discounts' => $this->getDiscountValues($cartTotals, $quote),
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
                'amount' => ['value' => $appliedTax['amount'], 'currency' => $currency]
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
            'amount' => ['value' => $total->getDiscountAmount(), 'currency' => $currency]
        ];
    }

    /**
     * Get Discount Values
     *
     * @param Total $total
     * @param Quote $quote
     * @return array
     */
    private function getDiscountValues(Total $total, Quote $quote)
    {
        $discountValues=[];
        foreach ($total->getDiscountPerRule() as $value) {
            $discount = [];
            $amount = [];
            /**
             * @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
             */
            $discountData = $value['discount'];
            /**
             * @var \Magento\SalesRule\Model\Rule $rule $rule
             */
            $rule = $value['rule'];
            $discount['label'] = $rule->getStoreLabel($quote->getStore()) ?: __('Discount');
            $amount['value'] = $discountData->getAmount();
            $amount['currency'] = $quote->getQuoteCurrencyCode();
            $discount['amount'] = $amount;
            $discountValues[] = $discount;
        }
        return $discountValues;
    }
}
