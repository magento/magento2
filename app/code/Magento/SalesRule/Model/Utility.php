<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\SalesRule\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory;
use Magento\SalesRule\Model\Rule\CustomerFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Utility
{
    /**
     * @var array
     */
    protected $_roundingDeltas = [];

    /**
     * @var array
     */
    protected $_baseRoundingDeltas = [];

    /**
     * @var UsageFactory
     */
    protected $usageFactory;

    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var DataObjectFactory
     */
    protected $objectFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var ValidateCoupon
     */
    private $validateCoupon;

    /**
     * @param UsageFactory $usageFactory
     * @param CouponFactory $couponFactory
     * @param Rule\CustomerFactory $customerFactory
     * @param DataObjectFactory $objectFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param ValidateCoupon|null $validateCoupon
     */
    public function __construct(
        UsageFactory $usageFactory,
        CouponFactory $couponFactory,
        CustomerFactory $customerFactory,
        DataObjectFactory $objectFactory,
        PriceCurrencyInterface $priceCurrency,
        ?ValidateCoupon $validateCoupon = null
    ) {
        $this->couponFactory = $couponFactory;
        $this->customerFactory = $customerFactory;
        $this->usageFactory = $usageFactory;
        $this->objectFactory = $objectFactory;
        $this->priceCurrency = $priceCurrency;
        $this->validateCoupon = $validateCoupon ?: ObjectManager::getInstance()->get(ValidateCoupon::class);
    }

    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param Rule $rule
     * @param Address $address
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function canProcessRule(Rule $rule, Address $address): bool
    {
        if ($rule->hasIsValidForAddress($address) && !$address->isObjectNew()) {
            return $rule->getIsValidForAddress($address);
        }

        if (!$this->validateCoupon->execute($rule, $address, $address->getQuote()->getCouponCode())) {
            return false;
        }

        /**
         * check per rule usage limit
         */
        $ruleId = $rule->getId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId = $address->getQuote()->getCustomerId();
            /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
            $ruleCustomer = $this->customerFactory->create();
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    $rule->setIsValidForAddress($address, false);
                    return false;
                }
            }
        }
        $rule->afterLoad();
        /**
         * When the rule has item-level actions (e.g. "Apply to SKU is not X"), address-level
         * conditions (subtotal, total qty, weight) must be evaluated against eligible items
         * only, so that excluded items do not count toward the condition threshold.
         */
        $savedTotals = null;
        if ($this->ruleHasItemRestrictions($rule) && $this->hasEligibleLineItemsForRule($rule, $address)) {
            $savedTotals = $this->setEligibleItemsTotalsOnAddress($rule, $address);
        }
        try {
            if (!$rule->validate($address)) {
                $rule->setIsValidForAddress($address, false);
                return false;
            }
        } finally {
            if ($savedTotals !== null) {
                $this->restoreAddressTotals($address, $savedTotals);
            }
        }
        /**
         * passed all validations, remember to be valid
         */
        $rule->setIsValidForAddress($address, true);
        return true;
    }

    /**
     * Check if the rule has item-level action conditions (e.g. apply to specific SKUs)
     *
     * @param Rule $rule
     * @return bool
     */
    private function ruleHasItemRestrictions(Rule $rule): bool
    {
        try {
            $actions = $rule->getActions();
        } catch (\Throwable $e) {
            return false;
        }
        if (!$actions instanceof \Magento\Rule\Model\Condition\Combine) {
            return false;
        }
        $conditions = $actions->getConditions();

        return !empty($conditions) && is_array($conditions);
    }

    /**
     * Whether at least one cart line item matches the rule's actions (eligible for the discount on items)
     *
     * @param Rule $rule
     * @param Address $address
     * @return bool
     */
    private function hasEligibleLineItemsForRule(Rule $rule, Address $address): bool
    {
        foreach ($address->getAllItems() as $item) {
            if ($this->isItemEligibleForRuleTotals($item, $rule)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compute totals for matching items, set them on the address, and return saved totals for later restoration
     *
     * @param Rule $rule
     * @param Address $address
     * @return array Saved address totals to restore after validation
     */
    private function setEligibleItemsTotalsOnAddress(Rule $rule, Address $address): array
    {
        $baseSubtotal = $baseSubtotalInclTax = $totalQty = $weight = 0;

        foreach ($address->getAllItems() as $item) {
            if (!$this->isItemEligibleForRuleTotals($item, $rule)) {
                continue;
            }
            $baseSubtotal += (float) $item->getBaseRowTotal();
            $baseSubtotalInclTax += (float) $item->getBaseRowTotalInclTax();
            $totalQty += (float) $item->getQty();
            $weight += (float) $item->getRowWeight();
        }

        $saved = [
            'base_subtotal' => $address->getBaseSubtotal(),
            'base_subtotal_with_discount' => $address->getBaseSubtotalWithDiscount(),
            'base_subtotal_total_incl_tax' => $address->getBaseSubtotalTotalInclTax(),
            'total_qty' => $address->getTotalQty(),
            'weight' => $address->getWeight(),
        ];

        $address->setBaseSubtotal($baseSubtotal);
        $address->setBaseSubtotalWithDiscount($baseSubtotal);
        $address->setBaseSubtotalTotalInclTax($baseSubtotalInclTax);
        $address->setTotalQty($totalQty);
        $address->setWeight($weight);

        return $saved;
    }

    /**
     * Whether the item counts toward rule condition totals (matches actions, no double-count)
     *
     * @param AbstractItem $item
     * @param Rule $rule
     * @return bool
     */
    private function isItemEligibleForRuleTotals(AbstractItem $item, Rule $rule): bool
    {
        if (($item->getParentItem() && $item->getParentItem()->getProductType() === 'configurable') ||
            (($item->getHasChildren() || $item->getChildren()) && $item->isChildrenCalculated()) ||
            ($item->getNoDiscount())) {
            return false;
        }

        return $rule->getActions()->validate($item);
    }

    /**
     * Restore address totals after rule condition validation
     *
     * @param Address $address
     * @param array $savedTotals
     * @return void
     */
    private function restoreAddressTotals(Address $address, array $savedTotals): void
    {
        $address->setBaseSubtotal($savedTotals['base_subtotal'] ?? 0);
        $address->setBaseSubtotalWithDiscount($savedTotals['base_subtotal_with_discount'] ?? 0);
        $address->setBaseSubtotalTotalInclTax($savedTotals['base_subtotal_total_incl_tax'] ?? 0);
        $address->setTotalQty($savedTotals['total_qty'] ?? 0);
        $address->setWeight($savedTotals['weight'] ?? 0);
    }

    /**
     * Set discount amount (found min)
     *
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return void
     */
    public function minFix(
        \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        $itemPrice = $this->getItemPrice($item);
        $baseItemPrice = $this->getItemBasePrice($item);

        $itemDiscountAmount = $item->getDiscountAmount();
        $itemBaseDiscountAmount = $item->getBaseDiscountAmount();

        $discountAmount = min($itemDiscountAmount + $discountData->getAmount(), $itemPrice * $item->getQty());
        $baseDiscountAmount =
            min($itemBaseDiscountAmount + $discountData->getBaseAmount(), $baseItemPrice * $item->getQty());

        $discountData->setAmount($discountAmount);
        $discountData->setBaseAmount($baseDiscountAmount);
    }

    /**
     * Process "delta" rounding
     *
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    public function deltaRoundingFix(
        \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        $discountAmount = $discountData->getAmount();
        $baseDiscountAmount = $discountData->getBaseAmount();
        $rowTotalInclTax = $item->getRowTotalInclTax();
        $baseRowTotalInclTax = $item->getBaseRowTotalInclTax();

        $percentKey = (string)$item->getDiscountPercent();
        $rowTotal = $item->getRowTotal();
        if ($percentKey && $rowTotal > 0) {
            $delta = isset($this->_roundingDeltas[$percentKey]) ? $this->_roundingDeltas[$percentKey] : 0;
            $baseDelta = isset($this->_baseRoundingDeltas[$percentKey]) ? $this->_baseRoundingDeltas[$percentKey] : 0;

            $discountAmount += $delta;
            $baseDiscountAmount += $baseDelta;

            $this->_roundingDeltas[$percentKey] = $discountAmount - $this->priceCurrency->round($discountAmount);
            $this->_baseRoundingDeltas[$percentKey] = $baseDiscountAmount
                - $this->priceCurrency->round($baseDiscountAmount);
        }

        /**
         * When we have 100% discount check if totals will not be negative
         */

        if ($item->getDiscountPercent() == 100) {
            $discountDelta = $rowTotalInclTax - $discountAmount;
            $baseDiscountDelta = $baseRowTotalInclTax - $baseDiscountAmount;

            if ($discountDelta < 0) {
                $discountAmount += $discountDelta;
            }

            if ($baseDiscountDelta < 0) {
                $baseDiscountAmount += $baseDiscountDelta;
            }
        }

        $discountData->setAmount($this->priceCurrency->round($discountAmount));
        $discountData->setBaseAmount($this->priceCurrency->round($baseDiscountAmount));

        return $this;
    }

    /**
     * Return item price
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemPrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        $calcPrice = $item->getCalculationPrice();
        return $price === null ? $calcPrice : $price;
    }

    /**
     * Return item base price
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        return $price !== null ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }

    /**
     * Return discount item qty
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param Rule $rule
     * @return int
     */
    public function getItemQty($item, $rule)
    {
        $qty = $item->getTotalQty();
        $discountQty = $rule->getDiscountQty();
        return $discountQty ? min($qty, $discountQty) : $qty;
    }

    /**
     * Merge two sets of ids
     *
     * @param array|string $a1
     * @param array|string $a2
     * @param bool $asString
     * @return array|string
     */
    public function mergeIds($a1, $a2, $asString = true)
    {
        if (!is_array($a1)) {
            $a1 = empty($a1) ? [] : explode(',', $a1);
        }
        if (!is_array($a2)) {
            $a2 = empty($a2) ? [] : explode(',', $a2);
        }
        $a = array_unique(array_merge($a1, $a2));
        if ($asString) {
            $a = implode(',', $a);
        }
        return $a;
    }

    /**
     * Resets rounding deltas data.
     *
     * @return void
     */
    public function resetRoundingDeltas()
    {
        $this->_roundingDeltas = [];
        $this->_baseRoundingDeltas = [];
    }
}
