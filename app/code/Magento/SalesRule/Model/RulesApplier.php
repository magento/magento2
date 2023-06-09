<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model;

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
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\SalesRule\Model\Utility
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
     * @var array
     */
    private $discountAggregator;

    /**
     * RulesApplier constructor.
     * @param CalculatorFactory $calculatorFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Utility $utility
     * @param ChildrenValidationLocator|null $childrenValidationLocator
     * @param DataFactory|null $discountDataFactory
     * @param RuleDiscountInterfaceFactory|null $discountInterfaceFactory
     * @param DiscountDataInterfaceFactory|null $discountDataInterfaceFactory
     */
    public function __construct(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\SalesRule\Model\Utility $utility,
        ChildrenValidationLocator $childrenValidationLocator = null,
        DataFactory $discountDataFactory = null,
        RuleDiscountInterfaceFactory $discountInterfaceFactory = null,
        DiscountDataInterfaceFactory $discountDataInterfaceFactory = null
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
    }

    /**
     * Apply rules to current order item
     *
     * @param AbstractItem $item
     * @param array $rules
     * @param bool $skipValidation
     * @param mixed $couponCode
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function applyRules($item, $rules, $skipValidation, $couponCode)
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

            $this->applyRule($item, $rule, $address, $couponCode);
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();
        }

        return $appliedRuleIds;
    }

    /**
     * Add rule discount description label to address object
     *
     * @param Address $address
     * @param Rule $rule
     * @return $this
     */
    public function addDiscountDescription($address, $rule)
    {
        $description = $address->getDiscountDescriptionArray();
        $ruleLabel = $rule->getStoreLabel($address->getQuote()->getStore());
        $label = '';
        if ($ruleLabel) {
            $label = $ruleLabel;
        } else {
            if ($address->getCouponCode() !== null && strlen($address->getCouponCode())) {
                $label = $address->getCouponCode();

                if ($rule->getDescription()) {
                    $label = $rule->getDescription();
                }
            }
        }

        if (strlen($label)) {
            $description[$rule->getId()] = $label;
        }

        $address->setDiscountDescriptionArray($description);

        return $this;
    }

    /**
     * Apply Rule
     *
     * @param AbstractItem $item
     * @param Rule $rule
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param mixed $couponCode
     * @return $this
     */
    protected function applyRule($item, $rule, $address, $couponCode)
    {
        if ($item->getChildren() && $item->isChildrenCalculated()) {
            $cloneItem = clone $item;
            /**
             * validate without children
             */
            $applyAll = $rule->getActions()->validate($cloneItem);
            foreach ($item->getChildren() as $childItem) {
                if ($applyAll || $rule->getActions()->validate($childItem)) {
                    $discountData = $this->getDiscountData($childItem, $rule, $address);
                    $this->setDiscountData($discountData, $childItem);
                }
            }
        } else {
            $discountData = $this->getDiscountData($item, $rule, $address);
            $this->setDiscountData($discountData, $item);
        }

        $this->maintainAddressCouponCode($address, $rule, $couponCode);
        $this->addDiscountDescription($address, $rule);

        return $this;
    }

    /**
     * Get discount Data
     *
     * @param AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return Data
     */
    protected function getDiscountData($item, $rule, $address)
    {
        $qty = $this->validatorUtility->getItemQty($item, $rule);

        $discountCalculator = $this->calculatorFactory->create($rule->getSimpleAction());
        $qty = $discountCalculator->fixQuantity($qty, $rule);
        $discountData = $discountCalculator->calculate($rule, $item, $qty);
        $this->eventFix($discountData, $item, $rule, $qty);
        $this->validatorUtility->deltaRoundingFix($discountData, $item);
        $this->setDiscountBreakdown($discountData, $item, $rule, $address);

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
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    private function setDiscountBreakdown($discountData, $item, $rule, $address)
    {
        if ($discountData->getAmount() > 0 && $item->getExtensionAttributes()) {
            $data = [
                'amount' => $discountData->getAmount(),
                'base_amount' => $discountData->getBaseAmount(),
                'original_amount' => $discountData->getOriginalAmount(),
                'base_original_amount' => $discountData->getBaseOriginalAmount()
            ];
            $itemDiscount = $this->discountDataInterfaceFactory->create(['data' => $data]);
            $ruleLabel = $rule->getStoreLabel($address->getQuote()->getStore()) ?: __('Discount');
            $data = [
                'discount' => $itemDiscount,
                'rule' => $ruleLabel,
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
