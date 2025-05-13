<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Validator;
use Magento\Quote\Model\Quote\Item;

/**
 * Calculates discount for cart item if fixed discount applied on whole cart.
 */
class CartFixed extends AbstractDiscount
{
    /**
     * Store information about addresses which cart fixed rule applied for
     *
     * @var int[]
     */
    protected $_cartFixedRuleUsedForAddress = [];

    /**
     * @var DeltaPriceRound
     */
    private DeltaPriceRound $deltaPriceRound;

    /**
     * @var CartFixedDiscount
     */
    private CartFixedDiscount $cartFixedDiscountHelper;

    /**
     * @var string
     */
    private static $discountType = 'CartFixed';

    /**
     * @var ExistingDiscountRuleCollector
     */
    private ExistingDiscountRuleCollector $existingDiscountRuleCollector;

    /**
     * @param Validator $validator
     * @param DataFactory $discountDataFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param DeltaPriceRound $deltaPriceRound
     * @param ExistingDiscountRuleCollector $existingDiscountRuleCollector
     * @param CartFixedDiscount|null $cartFixedDiscount
     */
    public function __construct(
        Validator $validator,
        DataFactory $discountDataFactory,
        PriceCurrencyInterface $priceCurrency,
        DeltaPriceRound $deltaPriceRound,
        ExistingDiscountRuleCollector $existingDiscountRuleCollector,
        ?CartFixedDiscount $cartFixedDiscount = null
    ) {
        $this->deltaPriceRound = $deltaPriceRound;
        $this->existingDiscountRuleCollector = $existingDiscountRuleCollector;
        $this->cartFixedDiscountHelper = $cartFixedDiscount ?:
            ObjectManager::getInstance()->get(CartFixedDiscount::class);
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
    }

    /**
     * Fixed discount for cart calculation
     *
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @return Data
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function calculate($rule, $item, $qty)
    {
        $ruleTotals = $this->validator->getRuleItemTotalsInfo($rule->getId());
        $baseRuleTotals = $ruleTotals['base_items_price'] ?? 0.0;
        $ruleItemsCount = $ruleTotals['items_count'] ?? 0;

        $address = $item->getAddress();
        $quote = $item->getQuote();
        $shippingMethod = $address->getShippingMethod();
        $isAppliedToShipping = (int) $rule->getApplyToShipping();
        $ruleDiscount = (float) $rule->getDiscountAmount();

        $isMultiShipping = $this->cartFixedDiscountHelper->checkMultiShippingQuote($quote);
        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);
        $baseItemDiscountAmount = (float) $item->getBaseDiscountAmount();

        $cartRules = $quote->getCartFixedRules();
        if (!isset($cartRules[$rule->getId()])) {
            $cartRules[$rule->getId()] = $rule->getDiscountAmount();
        }
        $availableDiscountAmount = (float) $cartRules[$rule->getId()];
        $discountType = self::$discountType . $rule->getId();

        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();
        if ($availableDiscountAmount > 0) {
            $store = $quote->getStore();
            $shippingPrice = $this->cartFixedDiscountHelper->applyDiscountOnPricesIncludedTax()
                ? (float) $address->getShippingInclTax()
                : (float) $address->getShippingExclTax();
            $baseRuleTotals = $shippingMethod ?
                $this->cartFixedDiscountHelper
                    ->getBaseRuleTotals(
                        $isAppliedToShipping,
                        $quote,
                        $isMultiShipping,
                        $address,
                        $baseRuleTotals,
                        $shippingPrice
                    ) : $baseRuleTotals;
            if ($isAppliedToShipping) {
                $baseDiscountAmount = $this->cartFixedDiscountHelper
                    ->getDiscountAmount(
                        $ruleDiscount,
                        $qty,
                        $baseItemPrice,
                        $baseRuleTotals,
                        $discountType
                    );
            } else {
                $baseDiscountAmount = $this->cartFixedDiscountHelper
                    ->getDiscountedAmountProportionally(
                        $ruleDiscount,
                        $qty,
                        $baseItemPrice,
                        $baseItemDiscountAmount,
                        $baseRuleTotals -
                        $this->getItemsTotalDiscount($rule->getId(), $ruleTotals['affected_items']),
                        $discountType
                    );

            }
            $discountAmount = $this->priceCurrency->convert($baseDiscountAmount, $store);
            $baseDiscountAmount = min($baseItemPrice * $qty, $baseDiscountAmount);
            if ($ruleItemsCount <= 1) {
                $this->deltaPriceRound->reset($discountType);
                if ($baseDiscountAmount > $availableDiscountAmount) {
                    $baseDiscountAmount = $availableDiscountAmount;
                }
            } else {
                $this->validator->decrementRuleItemTotalsCount($rule->getId());
            }

            $baseDiscountAmount = $this->priceCurrency->roundPrice($baseDiscountAmount);

            $availableDiscountAmount = $this->cartFixedDiscountHelper
                ->getAvailableDiscountAmount(
                    $rule,
                    $quote,
                    $isMultiShipping,
                    $cartRules,
                    $baseDiscountAmount,
                    $availableDiscountAmount
                );
            $cartRules[$rule->getId()] = $availableDiscountAmount;
            if ($isAppliedToShipping &&
                $isMultiShipping &&
                $ruleTotals['items_count'] <= 1) {
                $estimatedShippingAmount = (float) $address->getBaseShippingInclTax();
                $shippingDiscountAmount = $this->cartFixedDiscountHelper->
                    getShippingDiscountAmount(
                        $rule,
                        $estimatedShippingAmount,
                        $baseRuleTotals
                    );
                $cartRules[$rule->getId()] -= $shippingDiscountAmount;
                if ($cartRules[$rule->getId()] < 0.0) {
                    $baseDiscountAmount += $cartRules[$rule->getId()];
                    $discountAmount += $cartRules[$rule->getId()];
                }
            }
            if ($availableDiscountAmount <= 0) {
                $this->deltaPriceRound->reset($discountType);
            }

            $discountData->setAmount($this->priceCurrency->roundPrice(min($itemPrice * $qty, $discountAmount)));
            $discountData->setBaseAmount($baseDiscountAmount);
            $discountData->setOriginalAmount(min($itemOriginalPrice * $qty, $discountAmount));
            $discountData->setBaseOriginalAmount($this->priceCurrency->roundPrice($baseItemOriginalPrice));
        }
        $quote->setCartFixedRules($cartRules);

        return $discountData;
    }

    /**
     * Get existing discount applied to affected items
     *
     * @param int $ruleId
     * @param array $affectedItems
     * @return float
     */
    private function getItemsTotalDiscount(int $ruleId, array $affectedItems): float
    {
        if ($this->existingDiscountRuleCollector->getExistingRuleDiscount($ruleId) === null) {
            $existingRuleDiscount = 0;
            /** @var Item $ruleItem */
            foreach ($affectedItems as $ruleItem) {
                $existingRuleDiscount += $ruleItem->getBaseDiscountAmount();
            }
            $this->existingDiscountRuleCollector->setExistingRuleDiscount($ruleId, $existingRuleDiscount);
        }

        return $this->existingDiscountRuleCollector->getExistingRuleDiscount($ruleId);
    }

    /**
     * Set information about usage cart fixed rule by quote address
     *
     * @deprecated 101.2.0 should be removed as it is not longer used
     * @see Nothing
     * @param int $ruleId
     * @param int $itemId
     * @return void
     */
    protected function setCartFixedRuleUsedForAddress($ruleId, $itemId)
    {
        $this->_cartFixedRuleUsedForAddress[$ruleId] = $itemId;
    }

    /**
     * Retrieve information about usage cart fixed rule by quote address
     *
     * @deprecated 101.2.0 should be removed as it is not longer used
     * @see Nothing
     * @param int $ruleId
     * @return int|null
     */
    protected function getCartFixedRuleUsedForAddress($ruleId)
    {
        if (isset($this->_cartFixedRuleUsedForAddress[$ruleId])) {
            return $this->_cartFixedRuleUsedForAddress[$ruleId];
        }
        return null;
    }
}
