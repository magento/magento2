<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    private $deltaPriceRound;

    /**
     * @var CartFixedDiscount
     */
    private $cartFixedDiscountHelper;

    /**
     * @var string
     */
    private static $discountType = 'CartFixed';

    /**
     * @param Validator $validator
     * @param DataFactory $discountDataFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param DeltaPriceRound $deltaPriceRound
     * @param CartFixedDiscount|null $cartFixedDiscount
     */
    public function __construct(
        Validator $validator,
        DataFactory $discountDataFactory,
        PriceCurrencyInterface $priceCurrency,
        DeltaPriceRound $deltaPriceRound,
        ?CartFixedDiscount $cartFixedDiscount = null
    ) {
        $this->deltaPriceRound = $deltaPriceRound;
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();

        $ruleTotals = $this->validator->getRuleItemTotalsInfo($rule->getId());
        $baseRuleTotals = $ruleTotals['base_items_price'] ?? 0.0;

        $address = $item->getAddress();
        $shippingMethod = $address->getShippingMethod();
        $isAppliedToShipping = (int) $rule->getApplyToShipping();
        $quote = $item->getQuote();
        $ruleDiscount = (float) $rule->getDiscountAmount();

        $isMultiShipping = $this->cartFixedDiscountHelper->checkMultiShippingQuote($quote);
        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        $cartRules = $quote->getCartFixedRules();
        if (!isset($cartRules[$rule->getId()])) {
            $cartRules[$rule->getId()] = $rule->getDiscountAmount();
        }
        $availableDiscountAmount = (float) $cartRules[$rule->getId()];
        $discountType = self::$discountType . $rule->getId();

        if ($availableDiscountAmount > 0) {
            $store = $quote->getStore();
            if ($ruleTotals['items_count'] <= 1) {
                $baseRuleTotals = $shippingMethod ?
                    $this->cartFixedDiscountHelper
                        ->getBaseRuleTotals(
                            $isAppliedToShipping,
                            $quote,
                            $isMultiShipping,
                            $address,
                            $baseRuleTotals
                        ) : $baseRuleTotals;
                $maximumItemDiscount = $this->cartFixedDiscountHelper
                    ->getDiscountAmount(
                        $ruleDiscount,
                        $qty,
                        $baseItemPrice,
                        $baseRuleTotals,
                        $discountType
                    );
                $quoteAmount = $this->priceCurrency->convert($maximumItemDiscount, $store);
                $baseDiscountAmount = min($baseItemPrice * $qty, $maximumItemDiscount);
                $this->deltaPriceRound->reset($discountType);
            } else {
                $baseRuleTotals = $shippingMethod ?
                    $this->cartFixedDiscountHelper
                        ->getBaseRuleTotals(
                            $isAppliedToShipping,
                            $quote,
                            $isMultiShipping,
                            $address,
                            $baseRuleTotals
                        ) : $baseRuleTotals;
                $maximumItemDiscount =$this->cartFixedDiscountHelper
                    ->getDiscountAmount(
                        $ruleDiscount,
                        $qty,
                        $baseItemPrice,
                        $baseRuleTotals,
                        $discountType
                    );
                $quoteAmount = $this->priceCurrency->convert($maximumItemDiscount, $store);
                $baseDiscountAmount = min($baseItemPrice * $qty, $maximumItemDiscount);
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
                    $quoteAmount += $cartRules[$rule->getId()];
                }
            }
            if ($availableDiscountAmount <= 0) {
                $this->deltaPriceRound->reset($discountType);
            }

            $discountData->setAmount($this->priceCurrency->roundPrice(min($itemPrice * $qty, $quoteAmount)));
            $discountData->setBaseAmount($baseDiscountAmount);
            $discountData->setOriginalAmount(min($itemOriginalPrice * $qty, $quoteAmount));
            $discountData->setBaseOriginalAmount($this->priceCurrency->roundPrice($baseItemOriginalPrice));
        }
        $quote->setCartFixedRules($cartRules);

        return $discountData;
    }

    /**
     * Set information about usage cart fixed rule by quote address
     *
     * @deprecated 101.2.0 should be removed as it is not longer used
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
