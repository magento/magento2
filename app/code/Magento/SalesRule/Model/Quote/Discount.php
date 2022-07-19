<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Quote;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Api\Data\DiscountDataInterface;
use Magento\SalesRule\Api\Data\DiscountDataInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleDiscountInterfaceFactory;
use Magento\SalesRule\Model\Data\RuleDiscount;
use Magento\SalesRule\Model\Discount\PostProcessorFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Validator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\SalesRule\Model\RulesApplier;

/**
 * Discount totals calculation model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Discount extends AbstractTotal
{
    public const COLLECTOR_TYPE_CODE = 'discount';

    /**
     * Discount calculation object
     *
     * @var Validator
     */
    protected $calculator;

    /**
     * Core event manager proxy
     *
     * @var ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var RuleDiscountInterfaceFactory
     */
    private $discountInterfaceFactory;

    /**
     * @var DiscountDataInterfaceFactory
     */
    private $discountDataInterfaceFactory;

    /**
     * @var RulesApplier|null
     */
    private $rulesApplier;

    /**
     * @var array
     */
    private $addressDiscountAggregator = [];

    /**
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Validator $validator
     * @param PriceCurrencyInterface $priceCurrency
     * @param RuleDiscountInterfaceFactory|null $discountInterfaceFactory
     * @param DiscountDataInterfaceFactory|null $discountDataInterfaceFactory
     * @param RulesApplier|null $rulesApplier
     */
    public function __construct(
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Validator $validator,
        PriceCurrencyInterface $priceCurrency,
        RuleDiscountInterfaceFactory $discountInterfaceFactory = null,
        DiscountDataInterfaceFactory $discountDataInterfaceFactory = null,
        RulesApplier $rulesApplier = null
    ) {
        $this->setCode(self::COLLECTOR_TYPE_CODE);
        $this->eventManager = $eventManager;
        $this->calculator = $validator;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->discountInterfaceFactory = $discountInterfaceFactory
            ?: ObjectManager::getInstance()->get(RuleDiscountInterfaceFactory::class);
        $this->discountDataInterfaceFactory = $discountDataInterfaceFactory
            ?: ObjectManager::getInstance()->get(DiscountDataInterfaceFactory::class);
        $this->rulesApplier = $rulesApplier
            ?: ObjectManager::getInstance()->get(RulesApplier::class);
    }

    /**
     * Collect address discount amount
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $store = $this->storeManager->getStore($quote->getStoreId());
        /** @var Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();
        if ($quote->currentPaymentWasSet()) {
            $address->setPaymentMethod($quote->getPayment()->getMethod());
        }
        $this->calculator->reset($address);
        $itemsAggregate = [];
        foreach ($shippingAssignment->getItems() as $item) {
            $itemId = $item->getId();
            $itemsAggregate[$itemId] = $item;
        }
        $items = [];
        foreach ($quote->getAllAddresses() as $quoteAddress) {
            foreach ($quoteAddress->getAllItems() as $item) {
                $items[] = $item;
            }
        }
        if (!$items || !$itemsAggregate) {
            return $this;
        }
        $eventArgs = [
            'website_id' => $store->getWebsiteId(),
            'customer_group_id' => $quote->getCustomerGroupId(),
            'coupon_code' => $quote->getCouponCode(),
        ];
        $address->setDiscountDescription([]);
        $address->getExtensionAttributes()->setDiscounts([]);
        $this->addressDiscountAggregator = [];
        $address->setCartFixedRules([]);
        $quote->setCartFixedRules([]);
        foreach ($items as $item) {
            $this->rulesApplier->setAppliedRuleIds($item, []);
            if ($item->getExtensionAttributes()) {
                $item->getExtensionAttributes()->setDiscounts(null);
            }
            $item->setDiscountAmount(0);
            $item->setBaseDiscountAmount(0);
            $item->setDiscountPercent(0);
            if ($item->getChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $child->setDiscountAmount(0);
                    $child->setBaseDiscountAmount(0);
                    $child->setDiscountPercent(0);
                }
            }
        }
        $this->calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());
        $this->calculator->initTotals($items, $address);
        $items = $this->calculator->sortItemsByPriority($items, $address);
        $rules = $this->calculator->getRules($address);
        /** @var Rule $rule */
        foreach ($rules as $rule) {
            /** @var Item $item */
            foreach ($items as $item) {
                if ($quote->getIsMultiShipping() && $item->getAddress()->getId() !== $address->getId()) {
                    continue;
                }
                if ($item->getNoDiscount() || !$this->calculator->canApplyDiscount($item) || $item->getParentItem()) {
                    continue;
                }
                $eventArgs['item'] = $item;
                $this->eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);
                $this->calculator->process($item, $rule);
            }
            $appliedRuleIds = $quote->getAppliedRuleIds() ? explode(',', $quote->getAppliedRuleIds()) : [];
            if ($rule->getStopRulesProcessing() && in_array($rule->getId(), $appliedRuleIds)) {
                break;
            }
            $this->calculator->initTotals($items, $address);
        }
        foreach ($items as $item) {
            if (!isset($itemsAggregate[$item->getId()])) {
                continue;
            }
            if ($item->getParentItem()) {
                continue;
            } elseif ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $eventArgs['item'] = $child;
                    $this->eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);
                    $this->aggregateItemDiscount($child, $total);
                }
            }
            $this->aggregateItemDiscount($item, $total);
            if ($item->getExtensionAttributes()) {
                $this->aggregateDiscountPerRule($item, $address);
            }
        }
        $this->calculator->prepareDescription($address);
        $total->setDiscountDescription($address->getDiscountDescription());
        $total->setSubtotalWithDiscount($total->getSubtotal() + $total->getDiscountAmount());
        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $total->getBaseDiscountAmount());
        $address->setDiscountAmount($total->getDiscountAmount());
        $address->setBaseDiscountAmount($total->getBaseDiscountAmount());
        return $this;
    }

    /**
     * Aggregate item discount information to total data and related properties
     *
     * @param AbstractItem $item
     * @param Total $total
     * @return $this
     */
    protected function aggregateItemDiscount(
        AbstractItem $item,
        Total $total
    ) {
        $total->addTotalAmount($this->getCode(), -$item->getDiscountAmount());
        $total->addBaseTotalAmount($this->getCode(), -$item->getBaseDiscountAmount());
        return $this;
    }

    /**
     * Distribute discount at parent item to children items
     *
     * @param AbstractItem $item
     * @return $this
     * @deprecated No longer used.
     * @see \Magento\SalesRule\Model\RulesApplier::applyRule()
     */
    protected function distributeDiscount(AbstractItem $item)
    {
        $parentBaseRowTotal = $item->getBaseRowTotal();
        $keys = [
            'discount_amount',
            'base_discount_amount',
            'original_discount_amount',
            'base_original_discount_amount',
        ];
        $roundingDelta = [];
        foreach ($keys as $key) {
            //Initialize the rounding delta to a tiny number to avoid floating point precision problem
            $roundingDelta[$key] = 0.0000001;
        }
        foreach ($item->getChildren() as $child) {
            $ratio = $parentBaseRowTotal != 0 ? $child->getBaseRowTotal() / $parentBaseRowTotal : 0;
            foreach ($keys as $key) {
                if (!$item->hasData($key)) {
                    continue;
                }
                $value = $item->getData($key) * $ratio;
                $roundedValue = $this->priceCurrency->round($value + $roundingDelta[$key]);
                $roundingDelta[$key] += $value - $roundedValue;
                $child->setData($key, $roundedValue);
            }
        }

        foreach ($keys as $key) {
            $item->setData($key, 0);
        }
        return $this;
    }

    /**
     * Add discount total information to address
     *
     * @param Quote $quote
     * @param Total $total
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Total $total)
    {
        $result = null;
        $amount = $total->getDiscountAmount();

        if ($amount != 0) {
            $description = $total->getDiscountDescription() ?? '';
            $result = [
                'code' => $this->getCode(),
                'title' => strlen($description) ? __('Discount (%1)', $description) : __('Discount'),
                'value' => $amount
            ];
        }
        return $result;
    }

    /**
     * Aggregates discount per rule
     *
     * @param AbstractItem $item
     * @param AddressInterface $address
     * @return void
     */
    private function aggregateDiscountPerRule(
        AbstractItem $item,
        AddressInterface $address
    ) {
        $discountBreakdown = $item->getExtensionAttributes()->getDiscounts();
        if ($discountBreakdown) {
            foreach ($discountBreakdown as $value) {
                /* @var DiscountDataInterface $discount */
                $discount = $value->getDiscountData();
                $ruleLabel = $value->getRuleLabel();
                $ruleID = $value->getRuleID();
                if (isset($this->addressDiscountAggregator[$ruleID])) {
                    /** @var RuleDiscount $cartDiscount */
                    $cartDiscount = $this->addressDiscountAggregator[$ruleID];
                    $discountData = $cartDiscount->getDiscountData();
                    $discountData->setBaseAmount($discountData->getBaseAmount() + $discount->getBaseAmount());
                    $discountData->setAmount($discountData->getAmount() + $discount->getAmount());
                    $discountData->setOriginalAmount(
                        $discountData->getOriginalAmount() + $discount->getOriginalAmount()
                    );
                    $discountData->setBaseOriginalAmount(
                        $discountData->getBaseOriginalAmount() + $discount->getBaseOriginalAmount()
                    );
                } else {
                    $data = [
                        'amount' => $discount->getAmount(),
                        'base_amount' => $discount->getBaseAmount(),
                        'original_amount' => $discount->getOriginalAmount(),
                        'base_original_amount' => $discount->getBaseOriginalAmount()
                    ];
                    $discountData = $this->discountDataInterfaceFactory->create(['data' => $data]);
                    $data = [
                        'discount' => $discountData,
                        'rule' => $ruleLabel,
                        'rule_id' => $ruleID,
                    ];
                    /** @var RuleDiscount $cartDiscount */
                    $cartDiscount = $this->discountInterfaceFactory->create(['data' => $data]);
                    $this->addressDiscountAggregator[$ruleID] = $cartDiscount;
                }
            }
        }
        $address->getExtensionAttributes()->setDiscounts(array_values($this->addressDiscountAggregator));
    }
}
