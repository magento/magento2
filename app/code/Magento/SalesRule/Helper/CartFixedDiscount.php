<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\ScopeInterface;

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
     * @var ShippingMethodConverter
     */
    private $shippingMethodConverter = null;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig = null;

    /**
     * @param DeltaPriceRound $deltaPriceRound
     * @param PriceCurrencyInterface $priceCurrency
     * @param ShippingMethodConverter $shippingMethodConverter
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        DeltaPriceRound $deltaPriceRound,
        PriceCurrencyInterface $priceCurrency,
        ShippingMethodConverter $shippingMethodConverter,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->deltaPriceRound = $deltaPriceRound;
        $this->priceCurrency = $priceCurrency;
        $this->shippingMethodConverter = $shippingMethodConverter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve shipping amount by quote address and shipping method
     *
     * @param AddressInterface $address
     * @param float $shippingAmount
     * @return float
     */
    public function calculateShippingAmountWhenAppliedToShipping(
        AddressInterface $address,
        float $shippingAmount
    ): float {
        if ($shippingAmount == 0.0) {
            $addressQty = $this->getAddressQty($address);
            $address->setItemQty($addressQty);
            $address->setCollectShippingRates(true);
            $address->collectShippingRates();
            $shippingRates = $address->getAllShippingRates();
            foreach ($shippingRates as $shippingRate) {
                if ($shippingRate->getCode() === $address->getShippingMethod()
                ) {
                    $shippingMethod = $this->shippingMethodConverter
                        ->modelToDataObject($shippingRate, $address->getQuote()->getQuoteCurrencyCode());
                    $shippingAmount = $this->applyDiscountOnPricesIncludedTax()
                        ? $shippingMethod->getPriceInclTax()
                        : $shippingMethod->getPriceExclTax();
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
        $ratio = $baseRuleTotals != 0 ? $baseItemPrice * $qty / $baseRuleTotals : 0;
        return $this->deltaPriceRound->round(
            $ruleDiscount * $ratio,
            $discountType
        );
    }

    /**
     * Get discount amount for item calculated proportionally based on already applied discount
     *
     * @param float $ruleDiscount
     * @param float $qty
     * @param float $baseItemPrice
     * @param float $baseItemDiscountAmount
     * @param float $baseRuleTotalsDiscount
     * @param string $discountType
     * @return float
     */
    public function getDiscountedAmountProportionally(
        float $ruleDiscount,
        float $qty,
        float $baseItemPrice,
        float $baseItemDiscountAmount,
        float $baseRuleTotalsDiscount,
        string $discountType
    ): float {
        $baseItemPriceTotal = $baseItemPrice * $qty - $baseItemDiscountAmount;
        $ratio = $baseRuleTotalsDiscount != 0 ? $baseItemPriceTotal / $baseRuleTotalsDiscount : 0;
        $ratio = min($ratio, 1);

        return $this->deltaPriceRound->round($ruleDiscount * $ratio, $discountType);
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
        $ratio = $quoteBaseSubtotal != 0 ? $shippingAmount / $quoteBaseSubtotal : 0;
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
     * @param float $shippingAmount
     * @return float
     */
    public function getQuoteTotalsForRegularShipping(
        Quote\Address $address,
        float $baseRuleTotals,
        float $shippingAmount
    ): float {
        $baseRuleTotals += $this->calculateShippingAmountWhenAppliedToShipping(
            $address,
            $shippingAmount
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
     * @param float $shippingAmount
     * @return float
     */
    public function getBaseRuleTotals(
        int $isAppliedToShipping,
        Quote $quote,
        bool $isMultiShipping,
        Quote\Address $address,
        float $baseRuleTotals,
        float $shippingAmount
    ): float {
        if ($isAppliedToShipping) {
            $baseRuleTotals = ($quote->getIsMultiShipping() && $isMultiShipping) ?
                $this->getQuoteTotalsForMultiShipping($quote) :
                $this->getQuoteTotalsForRegularShipping($address, $baseRuleTotals, $shippingAmount);
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

    /**
     * Get configuration setting "Apply Discount On Prices Including Tax" value
     *
     * @return bool
     */
    public function applyDiscountOnPricesIncludedTax(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            'tax/calculation/discount_tax',
            ScopeInterface::SCOPE_STORE
        ) ?? false;
    }

    /**
     * Get address quantity.
     *
     * @param AddressInterface $address
     * @return float
     */
    private function getAddressQty(AddressInterface $address): float
    {
        $addressQty = 0;
        $items = array_filter(
            $address->getAllItems(),
            function ($item) {
                return !$item->getProduct()->isVirtual() && !$item->getParentItem();
            }
        );
        foreach ($items as $item) {
            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    if ($child->getProduct()->isVirtual()) {
                        continue;
                    }
                    $addressQty += $child->getTotalQty();
                }
            } else {
                $addressQty += (float)$item->getQty();
            }
        }

        return (float)$addressQty;
    }
}
