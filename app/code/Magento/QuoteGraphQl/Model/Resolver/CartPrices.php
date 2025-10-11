<?php
/**
 * Copyright 2025 Adobe
 * * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Quote\Api\Data\TotalsInterface as QuoteTotalsInterface;
use Magento\Quote\Api\Data\TotalsInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Cart\Totals as CartTotals;
use Magento\QuoteGraphQl\Model\Cart\TotalsCollector;
use Magento\Store\Model\ScopeInterface;

/**
 * @inheritdoc
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartPrices implements ResolverInterface
{
    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var string
     */
    private const QUERY_TYPE = 'query';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param TotalsCollector $totalsCollector
     * @param TotalsInterfaceFactory $totalsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        TotalsCollector $totalsCollector,
        private TotalsInterfaceFactory $totalsFactory,
        private DataObjectHelper $dataObjectHelper,
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
        $currency = $quote->getQuoteCurrencyCode();

        if (!$quote->isVirtual() && $info->operation->operation == self::QUERY_TYPE) {
            $addressTotalsData = $quote->getShippingAddress()->getData();
            $cartTotals = $this->totalsFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $cartTotals,
                $addressTotalsData,
                QuoteTotalsInterface::class
            );

            $appliedTaxes = $this->getAppliedTaxes($quote->getShippingAddress(), $currency);
        } else {
            /**
             * To calculate a right discount value
             * before calculate totals
             * need to reset Cart Fixed Rules in the quote
             */
            $quote->setCartFixedRules([]);
            $cartTotals = $this->totalsCollector->collectQuoteTotals($quote);
            $appliedTaxes = $this->getAppliedTaxes($cartTotals, $currency);
        }

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
     * @param \Magento\Quote\Model\Quote\Address|Total $addressOrTotals
     * @param string $currency
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getAppliedTaxes($addressOrTotals, string $currency): array
    {
        if (!$addressOrTotals instanceof Total && !$addressOrTotals instanceof \Magento\Quote\Model\Quote\Address) {
            throw new \InvalidArgumentException('Unsupported totals type: ' . get_class($addressOrTotals));
        }

        $appliedTaxesData = [];
        $appliedTaxes = $addressOrTotals->getAppliedTaxes();

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
     * @param Total|CartTotals $totals
     * @param string $currency
     * @return array|null
     * @throws \InvalidArgumentException
     */
    private function getDiscount($totals, string $currency)
    {
        $this->validateTotalsInstance($totals);

        if ($totals->getDiscountAmount() === 0) {
            return null;
        }
        return [
            'label' => $totals->getDiscountDescription() !== null ?
                explode(', ', $totals->getDiscountDescription()) : [],
            'amount' => ['value' => $totals->getDiscountAmount(), 'currency' => $currency]
        ];
    }

    /**
     * Get Subtotal with discount excluding tax.
     *
     * @param Total|CartTotals $totals
     * @return float
     * @throws \InvalidArgumentException
     */
    private function getSubtotalWithDiscountExcludingTax($totals): float
    {
        $this->validateTotalsInstance($totals);

        $discountIncludeTax = $this->scopeConfig->getValue(
            'tax/calculation/discount_tax',
            ScopeInterface::SCOPE_STORE
        ) ?? 0;
        $discountExclTax = $discountIncludeTax ?
            $totals->getDiscountAmount() + $totals->getDiscountTaxCompensationAmount() :
            $totals->getDiscountAmount();

        return $totals->getSubtotal() +  $discountExclTax;
    }

    /**
     * Validates the provided totals instance to ensure it is of a supported type.
     *
     * @param Total|CartTotals $totals
     * @return void
     * @throws \InvalidArgumentException If the provided totals instance is of an unsupported type.
     */
    private function validateTotalsInstance($totals)
    {

        if (!$totals instanceof Total && !$totals instanceof CartTotals) {
            throw new \InvalidArgumentException('Unsupported totals type: ' . get_class($totals));
        }
    }
}
