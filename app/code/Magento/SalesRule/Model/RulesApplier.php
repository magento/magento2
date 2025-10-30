<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\SalesRule\Model;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Data\RuleDiscount;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\Framework\App\ObjectManager;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Api\Data\RuleDiscountInterfaceFactory;
use Magento\SalesRule\Api\Data\DiscountDataInterfaceFactory;
use Magento\SalesRule\Api\Data\DiscountAppliedToInterface as DiscountAppliedTo;

/**
 * Rule applier model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RulesApplier
{
    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var Utility
     */
    protected $validatorUtility;

    /**
     * @var ChildrenValidationLocator
     */
    private $childrenValidationLocator;

    /**
     * @var CalculatorFactory
     */
    private $calculatorFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory
     */
    protected $discountFactory;

    /**
     * @var RuleDiscountInterfaceFactory
     */
    private $discountInterfaceFactory;

    /**
     * @var DiscountDataInterfaceFactory
     */
    private $discountDataInterfaceFactory;

    /**
     * @var SelectRuleCoupon
     */
    private $selectRuleCoupon;

    /**
     * @var array
     */
    private $discountAggregator;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param CalculatorFactory $calculatorFactory
     * @param ManagerInterface $eventManager
     * @param Utility $utility
     * @param ChildrenValidationLocator|null $childrenValidationLocator
     * @param DataFactory|null $discountDataFactory
     * @param RuleDiscountInterfaceFactory|null $discountInterfaceFactory
     * @param DiscountDataInterfaceFactory|null $discountDataInterfaceFactory
     * @param SelectRuleCoupon|null $selectRuleCoupon
     * @param PriceCurrencyInterface|null $priceCurrency
     */
    public function __construct(
        CalculatorFactory $calculatorFactory,
        ManagerInterface $eventManager,
        Utility $utility,
        ?ChildrenValidationLocator $childrenValidationLocator = null,
        ?DataFactory $discountDataFactory = null,
        ?RuleDiscountInterfaceFactory $discountInterfaceFactory = null,
        ?DiscountDataInterfaceFactory $discountDataInterfaceFactory = null,
        ?SelectRuleCoupon $selectRuleCoupon = null,
        ?PriceCurrencyInterface $priceCurrency = null
    ) {
        $this->calculatorFactory = $calculatorFactory;
        $this->validatorUtility = $utility;
        $this->_eventManager = $eventManager;
        $this->childrenValidationLocator = $childrenValidationLocator
             ?: ObjectManager::getInstance()->get(ChildrenValidationLocator::class);
        $this->discountFactory = $discountDataFactory ?: ObjectManager::getInstance()->get(DataFactory::class);
        $this->discountInterfaceFactory = $discountInterfaceFactory
            ?: ObjectManager::getInstance()->get(RuleDiscountInterfaceFactory::class);
        $this->discountDataInterfaceFactory = $discountDataInterfaceFactory
            ?: ObjectManager::getInstance()->get(DiscountDataInterfaceFactory::class);
        $this->selectRuleCoupon = $selectRuleCoupon
            ?: ObjectManager::getInstance()->get(SelectRuleCoupon::class);
        $this->priceCurrency = $priceCurrency ?: ObjectManager::getInstance()->get(PriceCurrencyInterface::class);
    }

    /**
     * Apply rules to current order item
     *
     * @param AbstractItem $item
     * @param array $rules
     * @param bool $skipValidation
     * @param string $couponCodes
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function applyRules($item, $rules, $skipValidation, array $couponCodes = [])
    {
        $address = $item->getAddress();
        $appliedRuleIds = [];
        /* @var $rule Rule */
        foreach ($rules as $rule) {
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }
            if (!$skipValidation && !$rule->getActions()->validate($item)) {
                if (!$this->childrenValidationLocator->isChildrenValidationRequired($item)) {
                    continue;
                }
                $childItems = $item->getChildren();
                $isContinue = true;
                if (!empty($childItems)) {
                    foreach ($childItems as $childItem) {
                        if ($rule->getActions()->validate($childItem)) {
                            $isContinue = false;
                        }
                    }
                }
                if ($isContinue) {
                    continue;
                }
            }

            $this->applyRule($item, $rule, $address, $couponCodes);
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();
        }

        return $appliedRuleIds;
    }

    /**
     * Add rule discount description label to address object
     *
     * @param Address $address
     * @param Rule $rule
     * @param array $couponCodes
     * @return $this
     */
    public function addDiscountDescription($address, $rule, array $couponCodes = [])
    {
        $description = $address->getDiscountDescriptionArray();
        $label = $this->getRuleLabel($address, $rule, $couponCodes);

        if (strlen($label)) {
            $description[$rule->getId()] = $label;
        }

        $address->setDiscountDescriptionArray($description);

        return $this;
    }

    /**
     * Retrieve rule label
     *
     * @param Address $address
     * @param Rule $rule
     * @param array $couponCodes
     * @return string
     */
    private function getRuleLabel(Address $address, Rule $rule, array $couponCodes = []): string
    {
        $ruleLabel = $rule->getStoreLabel($address->getQuote()->getStore());
        if ($ruleLabel) {
            return $ruleLabel;
        }
        $ruleCoupon = $this->selectRuleCoupon->execute($rule, $couponCodes);
        if ($ruleCoupon) {
            if ($rule->getDescription()) {
                return $rule->getDescription();
            }
            return $ruleCoupon;
        }
        return '';
    }

    /**
     * Add rule shipping discount description label to address object
     *
     * @param Address $address
     * @param Rule $rule
     * @param array $discount
     * @param array $couponCodes
     * @return void
     */
    public function addShippingDiscountDescription(
        Address $address,
        Rule $rule,
        array $discount,
        array $couponCodes
    ): void {
        $addressDiscounts = $address->getExtensionAttributes()->getDiscounts();
        $ruleLabel = $this->getRuleLabel($address, $rule, $couponCodes);
        $discount[DiscountAppliedTo::APPLIED_TO] = DiscountAppliedTo::APPLIED_TO_SHIPPING;
        $discountData = $this->discountDataInterfaceFactory->create(['data' => $discount]);
        $data = [
            'discount' => $discountData,
            'rule' => $ruleLabel,
            'rule_id' => $rule->getRuleId(),
        ];
        $addressDiscounts[] = $this->discountInterfaceFactory->create(['data' => $data]);
        $address->getExtensionAttributes()->setDiscounts($addressDiscounts);
    }

    /**
     * Apply Rule
     *
     * @param AbstractItem $item
     * @param Rule $rule
     * @param Address $address
     * @param string[] $couponCodes
     * @return $this
     */
    protected function applyRule($item, $rule, $address, array $couponCodes = [])
    {
        if ($item->getChildren() && $item->isChildrenCalculated()) {
            $cloneItem = clone $item;
            /**
             * Validates item without children to check whether the rule can be applied to the item itself
             * If the rule can be applied to the item, the discount is applied to the item itself and
             * distributed among its children
             */
            if ($rule->getActions()->validate($cloneItem)) {
                // Aggregate discount data from children
                $discountData = $this->getDiscountDataFromChildren($item);
                $this->setDiscountData($discountData, $item);
                // Calculate discount data based on parent item
                $discountData = $this->getDiscountData($item, $rule, $address, $couponCodes);
                $this->distributeDiscount($discountData, $item);
                // reset discount data in parent item after distributing discount to children
                $discountData = $this->discountFactory->create();
                $this->setDiscountData($discountData, $item);
            } else {
                foreach ($item->getChildren() as $childItem) {
                    if ($rule->getActions()->validate($childItem)) {
                        $discountData = $this->getDiscountData($childItem, $rule, $address, $couponCodes);
                        $this->setDiscountData($discountData, $childItem);
                    }
                }
            }
        } else {
            $discountData = $this->getDiscountData($item, $rule, $address, $couponCodes);
            $this->setDiscountData($discountData, $item);
        }

        $this->addDiscountDescription($address, $rule, $couponCodes);
        $this->maintainAddressCouponCode($address, $rule, $address->getQuote()->getCouponCode());

        return $this;
    }

    /**
     * Get discount data from children
     *
     * @param AbstractItem $item
     * @return Data
     */
    private function getDiscountDataFromChildren(AbstractItem $item): Data
    {
        $discountData = $this->discountFactory->create();

        foreach ($item->getChildren() as $child) {
            $discountData->setAmount($discountData->getAmount() + $child->getDiscountAmount());
            $discountData->setBaseAmount($discountData->getBaseAmount() + $child->getBaseDiscountAmount());
            $discountData->setOriginalAmount($discountData->getOriginalAmount() + $child->getOriginalDiscountAmount());
            $discountData->setBaseOriginalAmount(
                $discountData->getBaseOriginalAmount() + $child->getBaseOriginalDiscountAmount()
            );
        }

        return $discountData;
    }

    /**
     * Distributes discount applied from parent item to its children items
     *
     * This method originates from \Magento\SalesRule\Model\Quote\Discount::distributeDiscount()
     *
     * @param Data $discountData
     * @param AbstractItem $item
     * @see \Magento\SalesRule\Model\Quote\Discount::distributeDiscount()
     */
    private function distributeDiscount(Data $discountData, AbstractItem $item): void
    {
        $data = [
            'discount_amount' => $discountData->getAmount() - $item->getDiscountAmount(),
            'base_discount_amount' => $discountData->getBaseAmount() - $item->getBaseDiscountAmount(),
        ];

        $parentBaseRowTotal = max(0, $item->getBaseRowTotal() - $item->getBaseDiscountAmount());
        $keys = array_keys($data);
        $roundingDelta = [];
        foreach ($keys as $key) {
            //Initialize the rounding delta to a tiny number to avoid floating point precision problem
            $roundingDelta[$key] = 0.0000001;
        }
        foreach ($item->getChildren() as $child) {
            $childBaseRowTotalWithDiscount = max(0, $child->getBaseRowTotal() - $child->getBaseDiscountAmount());
            $ratio = min(1, $parentBaseRowTotal != 0 ? $childBaseRowTotalWithDiscount / $parentBaseRowTotal : 0);
            foreach ($keys as $key) {
                $value = $data[$key] * $ratio;
                $roundedValue = $this->priceCurrency->round($value + $roundingDelta[$key]);
                $roundingDelta[$key] += $value - $roundedValue;
                $child->setData($key, $child->getData($key) + $roundedValue);
            }
        }
    }

    /**
     * Get discount Data
     *
     * @param AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param Address $address
     * @param string[] $couponCodes
     * @return Data
     */
    protected function getDiscountData($item, $rule, $address, array $couponCodes = [])
    {
        $qty = $this->validatorUtility->getItemQty($item, $rule);

        $discountCalculator = $this->calculatorFactory->create($rule->getSimpleAction());
        $qty = $discountCalculator->fixQuantity($qty, $rule);
        $discountData = $discountCalculator->calculate($rule, $item, $qty);
        $this->eventFix($discountData, $item, $rule, $qty);
        $this->validatorUtility->deltaRoundingFix($discountData, $item);
        $this->setDiscountBreakdown($discountData, $item, $rule, $address, $couponCodes);

        /**
         * We can't use row total here because row total not include tax
         * Discount can be applied on price included tax
         */

        $this->validatorUtility->minFix($discountData, $item, $qty);

        return $discountData;
    }

    /**
     * Set Discount Breakdown
     *
     * @param Data $discountData
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param Address $address
     * @param string[] $couponCodes
     * @return $this
     */
    private function setDiscountBreakdown($discountData, $item, $rule, $address, array $couponCodes = [])
    {
        if ($discountData->getAmount() > 0 && $item->getExtensionAttributes()) {
            $data = [
                'amount' => $discountData->getAmount(),
                'base_amount' => $discountData->getBaseAmount(),
                'original_amount' => $discountData->getOriginalAmount(),
                'base_original_amount' => $discountData->getBaseOriginalAmount()
            ];
            $itemDiscount = $this->discountDataInterfaceFactory->create(['data' => $data]);
            $data = [
                'discount' => $itemDiscount,
                'rule' => $this->getRuleLabel($address, $rule, $couponCodes),
                'rule_id' => $rule->getId(),
            ];
            /** @var RuleDiscount $itemDiscount */
            $ruleDiscount = $this->discountInterfaceFactory->create(['data' => $data]);
            $this->discountAggregator[$item->getId()][$rule->getId()] = $ruleDiscount;
            $item->getExtensionAttributes()->setDiscounts(array_values($this->discountAggregator[$item->getId()]));
            $parentItem = $item->getParentItem();
            if ($parentItem && $parentItem->getExtensionAttributes()) {
                $this->aggregateDiscountBreakdown($discountData, $parentItem, $rule, $address);
            }
        }
        return $this;
    }

    /**
     * Reset discount aggregator
     */
    public function resetDiscountAggregator()
    {
        $this->discountAggregator = [];
    }

    /**
     * Add Discount Breakdown to existing discount data
     *
     * @param Data $discountData
     * @param AbstractItem $item
     * @param Rule $rule
     * @param Address $address
     */
    private function aggregateDiscountBreakdown(
        Data $discountData,
        AbstractItem $item,
        Rule $rule,
        Address $address
    ): void {
        $ruleLabel = $rule->getStoreLabel($address->getQuote()->getStore()) ?: __('Discount');
        /** @var RuleDiscount[] $discounts */
        $discounts = [];
        foreach ((array) $item->getExtensionAttributes()->getDiscounts() as $discount) {
            $discounts[$discount->getRuleID()] = $discount;
        }

        $data = [
            'amount' => $discountData->getAmount(),
            'base_amount' => $discountData->getBaseAmount(),
            'original_amount' => $discountData->getOriginalAmount(),
            'base_original_amount' => $discountData->getBaseOriginalAmount()
        ];

        $discount = $discounts[$rule->getId()] ?? null;

        if (isset($discount)) {
            $data['amount'] += $discount->getDiscountData()->getAmount();
            $data['base_amount'] += $discount->getDiscountData()->getBaseAmount();
            $data['original_amount'] += $discount->getDiscountData()->getOriginalAmount();
            $data['base_original_amount'] += $discount->getDiscountData()->getBaseOriginalAmount();
        }

        $discounts[$rule->getId()] = $this->discountInterfaceFactory->create(
            [
                'data' => [
                    'discount' => $this->discountDataInterfaceFactory->create(['data' => $data]),
                    'rule' => $ruleLabel,
                    'rule_id' => $rule->getId(),
                ]
            ]
        );
        $item->getExtensionAttributes()->setDiscounts(array_values($discounts));
    }

    /**
     * Set Discount data
     *
     * @param Data $discountData
     * @param AbstractItem $item
     * @return $this
     */
    protected function setDiscountData($discountData, $item)
    {
        $item->setDiscountAmount($discountData->getAmount());
        $item->setBaseDiscountAmount($discountData->getBaseAmount());
        $item->setOriginalDiscountAmount($discountData->getOriginalAmount());
        $item->setBaseOriginalDiscountAmount($discountData->getBaseOriginalAmount());

        return $this;
    }

    /**
     * Set coupon code to address if $rule contains validated coupon
     *
     * @param Address $address
     * @param Rule $rule
     * @param mixed $couponCode
     * @return $this
     */
    public function maintainAddressCouponCode($address, $rule, $couponCode)
    {
        /*
        Rule is a part of rules collection, which includes only rules with 'No Coupon' type or with validated coupon.
        As a result, if rule uses coupon code(s) ('Specific' or 'Auto' Coupon Type), it always contains validated coupon
        */
        if ($rule->getCouponType() != Rule::COUPON_TYPE_NO_COUPON) {
            $address->setCouponCode($couponCode);
        }

        return $this;
    }

    /**
     * Fire event to allow overwriting of discount amounts
     *
     * @param Data $discountData
     * @param AbstractItem $item
     * @param Rule $rule
     * @param float $qty
     * @return $this
     */
    protected function eventFix(
        Data $discountData,
        AbstractItem $item,
        Rule $rule,
        $qty
    ) {
        $quote = $item->getQuote();
        $address = $item->getAddress();

        $this->_eventManager->dispatch(
            'salesrule_validator_process',
            [
                'rule' => $rule,
                'item' => $item,
                'address' => $address,
                'quote' => $quote,
                'qty' => $qty,
                'result' => $discountData
            ]
        );

        return $this;
    }

    /**
     * Set Applied Rule Ids
     *
     * @param AbstractItem $item
     * @param int[] $appliedRuleIds
     * @return $this
     */
    public function setAppliedRuleIds(AbstractItem $item, array $appliedRuleIds)
    {
        $address = $item->getAddress();
        $quote = $item->getQuote();

        $item->setAppliedRuleIds($this->validatorUtility->mergeIds($item->getAppliedRuleIds(), $appliedRuleIds));
        $address->setAppliedRuleIds($this->validatorUtility->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->validatorUtility->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));

        return $this;
    }
}
