<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
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
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Quote\Api\Data\TotalsInterface as QuoteTotalsInterface;
use Magento\Quote\Api\Data\TotalsInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Cart\Totals as CartTotals;
use Magento\QuoteGraphQl\Model\Cart\TotalsCollector;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Address;

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
     * @var TotalsInterfaceFactory
     */
    private TotalsInterfaceFactory $totalsFactory;

    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @param TotalsCollector $totalsCollector
     * @param ScopeConfigInterface|null $scopeConfig
     * @param TotalsInterfaceFactory|null $totalsFactory
     * @param DataObjectHelper|null $dataObjectHelper
     * */
    public function __construct(
        TotalsCollector $totalsCollector,
        ?ScopeConfigInterface $scopeConfig = null,
        ?TotalsInterfaceFactory $totalsFactory = null,
        ?DataObjectHelper $dataObjectHelper = null
    ) {
        $this->totalsCollector = $totalsCollector;
        $this->scopeConfig = $scopeConfig ??  ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->totalsFactory = $totalsFactory ??
            ObjectManager::getInstance()->get(TotalsInterfaceFactory::class);
        $this->dataObjectHelper = $dataObjectHelper ??
            ObjectManager::getInstance()->get(DataObjectHelper::class);
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

        // check scenarios require force recollecting totals
        // discounts should return rule details, which are calculated as part of collectTotals
        $fieldSelection = $info->getFieldSelection(1);
        if (!$quote->isVirtual() && $quote->getTriggerRecollect() != 1 &&
            $info->operation->operation == self::QUERY_TYPE &&
            !array_key_exists('discounts', $fieldSelection) &&
            !array_key_exists('gift_options', $fieldSelection)
        ) {
            $addressTotalsData = $quote->getShippingAddress()->getData();
            unset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
            $cartTotals = $this->totalsFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $cartTotals,
                $addressTotalsData,
                QuoteTotalsInterface::class
            );

            if (isset($addressTotalsData['discount_description'])) {
                $cartTotals->setDiscountDescription($addressTotalsData['discount_description']);
            }

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
     * @param Address|Total $addressOrTotals
     * @param string $currency
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getAppliedTaxes(Address|Total $addressOrTotals, string $currency): array
    {
        if (!$addressOrTotals instanceof Total && !$addressOrTotals instanceof Address) {
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
    private function getDiscount(Total|CartTotals $totals, string $currency)
    {
        $this->validateTotalsInstance($totals);

        if ($totals->getDiscountAmount() == 0) {
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
    private function getSubtotalWithDiscountExcludingTax(Total|CartTotals $totals): float
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
    private function validateTotalsInstance($totals): void
    {

        if (!$totals instanceof Total && !$totals instanceof CartTotals) {
            throw new \InvalidArgumentException('Unsupported totals type: ' . get_class($totals));
        }
    }
}
