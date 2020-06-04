<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\SalesRule\Model\Rule;

/**
 * Helper for CartFixed Available Discount and Quote Totals
 */
class CartFixedDiscount
{
    /**
     * @var DeltaPriceRound
     */
    private $deltaPriceRound;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param DeltaPriceRound $deltaPriceRound
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        DeltaPriceRound $deltaPriceRound,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->deltaPriceRound = $deltaPriceRound;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Retrieve shipping amount by quote address and shipping method
     *
     * @param AddressInterface $address
     * @return float
     */
    public function calculateShippingAmountWhenAppliedToShipping(
        AddressInterface $address
    ): float {
        $shippingAmount = (float) $address->getShippingAmount();
        if ($shippingAmount == 0.0) {
            $address->setCollectShippingRates(true);
            $address->collectShippingRates();
            $shippingRates = $address->getAllShippingRates();
            foreach ($shippingRates as $shippingRate) {
                if ($shippingRate->getCode() === $address->getShippingMethod()
                ) {
                    $shippingAmount = (float) $shippingRate->getPrice();
                    break;
                }
            }
        }
        return $shippingAmount;
    }

    /**
     * Get the available discount amount calculated from the ration
     *
     * @param float $ruleDiscount
     * @param float $qty
     * @param float $baseItemPrice
     * @param float $baseRuleTotals
     * @param string $discountType
     * @return float
     */
    public function getDiscountAmount(
        float $ruleDiscount,
        float $qty,
        float $baseItemPrice,
        float $baseRuleTotals,
        string $discountType
    ): float {
        $ratio = $baseItemPrice * $qty / $baseRuleTotals;
        return $this->deltaPriceRound->round(
            $ruleDiscount * $ratio,
            $discountType
        );
    }

    /**
     * Get shipping discount amount
     *
     * @param Rule $rule
     * @param float $shippingAmount
     * @param float $quoteBaseSubtotal
     * @return float
     */
    public function getShippingDiscountAmount(
        Rule $rule,
        float $shippingAmount,
        float $quoteBaseSubtotal
    ): float {
        $ratio = $shippingAmount / $quoteBaseSubtotal;
        return $this->priceCurrency
            ->roundPrice(
                $rule->getDiscountAmount() * $ratio
            );
    }

    /**
     * Check if the current quote is multi shipping or not
     *
     * @param Quote $quote
     * @return bool
     */
    public function checkMultiShippingQuote(Quote $quote): bool
    {
        $isMultiShipping = false;
        $extensionAttributes = $quote->getExtensionAttributes();
        if (!$quote->isVirtual() &&
            $extensionAttributes &&
            $extensionAttributes->getShippingAssignments()) {
            $shippingAssignments = $extensionAttributes->getShippingAssignments();
            if (count($shippingAssignments) > 1) {
                $isMultiShipping = true;
            }
        }
        return $isMultiShipping;
    }

    /**
     * Get base rule totals for multi shipping addresses
     *
     * @param Quote $quote
     * @return float
     */
    public function getQuoteTotalsForMultiShipping(Quote $quote): float
    {
        $quoteTotal = $quote->getBaseSubtotal();
        $extensionAttributes = $quote->getExtensionAttributes();
        $shippingAssignments = $extensionAttributes->getShippingAssignments();
        $totalShippingPrice = 0.0;
        foreach ($shippingAssignments as $assignment) {
            $totalShippingPrice += $assignment->getShipping()->getAddress()->getBaseShippingInclTax();
        }
        return $quoteTotal + $totalShippingPrice;
    }

    /**
     * Get base rule totals for regular shipping address
     *
     * @param Quote\Address $address
     * @param float $baseRuleTotals
     * @return float
     */
    public function getQuoteTotalsForRegularShipping(
        Quote\Address $address,
        float $baseRuleTotals
    ): float {
        $baseRuleTotals += $this->calculateShippingAmountWhenAppliedToShipping(
            $address
        );
        return $baseRuleTotals;
    }

    /**
     * Get base rule totals
     *
     * @param int $isAppliedToShipping
     * @param Quote $quote
     * @param bool $isMultiShipping
     * @param Quote\Address $address
     * @param float $baseRuleTotals
     * @return float
     */
    public function getBaseRuleTotals(
        int $isAppliedToShipping,
        Quote $quote,
        bool $isMultiShipping,
        Quote\Address $address,
        float $baseRuleTotals
    ): float {
        if ($isAppliedToShipping) {
            $baseRuleTotals = ($quote->getIsMultiShipping() && $isMultiShipping) ?
                $this->getQuoteTotalsForMultiShipping($quote) :
                $this->getQuoteTotalsForRegularShipping($address, $baseRuleTotals);
        } else {
            if ($quote->getIsMultiShipping() && $isMultiShipping) {
                $baseRuleTotals = $quote->getBaseSubtotal();
            }
        }
        return (float) $baseRuleTotals;
    }

    /**
     * Get available discount amount
     *
     * @param Rule $rule
     * @param Quote $quote
     * @param bool $isMultiShipping
     * @param array $cartRules
     * @param float $baseDiscountAmount
     * @param float $availableDiscountAmount
     * @return float
     */
    public function getAvailableDiscountAmount(
        Rule $rule,
        Quote $quote,
        bool $isMultiShipping,
        array $cartRules,
        float $baseDiscountAmount,
        float $availableDiscountAmount
    ): float {
        if ($quote->getIsMultiShipping() && $isMultiShipping) {
            $availableDiscountAmount = (float)$cartRules[$rule->getId()] - $baseDiscountAmount;
        } else {
            $availableDiscountAmount -= $baseDiscountAmount;
        }
        return $availableDiscountAmount;
    }
}
