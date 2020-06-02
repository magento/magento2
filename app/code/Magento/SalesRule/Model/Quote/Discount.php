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
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Api\Data\DiscountDataInterface;
use Magento\SalesRule\Api\Data\DiscountDataInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleDiscountInterfaceFactory;
use Magento\SalesRule\Model\Data\RuleDiscount;
use Magento\SalesRule\Model\Discount\PostProcessorFactory;
use Magento\SalesRule\Model\Validator;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Discount totals calculation model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Discount extends AbstractTotal
{
    const COLLECTOR_TYPE_CODE = 'discount';

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
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Validator $validator
     * @param PriceCurrencyInterface $priceCurrency
     * @param RuleDiscountInterfaceFactory|null $discountInterfaceFactory
     * @param DiscountDataInterfaceFactory|null $discountDataInterfaceFactory
     */
    public function __construct(
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Validator $validator,
        PriceCurrencyInterface $priceCurrency,
        RuleDiscountInterfaceFactory $discountInterfaceFactory = null,
        DiscountDataInterfaceFactory $discountDataInterfaceFactory = null
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
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $store = $this->storeManager->getStore($quote->getStoreId());
        $address = $shippingAssignment->getShipping()->getAddress();

        if ($quote->currentPaymentWasSet()) {
            $address->setPaymentMethod($quote->getPayment()->getMethod());
        }

        $this->calculator->reset($address);

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $eventArgs = [
            'website_id' => $store->getWebsiteId(),
            'customer_group_id' => $quote->getCustomerGroupId(),
            'coupon_code' => $quote->getCouponCode(),
        ];

        $this->calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());
        $this->calculator->initTotals($items, $address);

        $address->setDiscountDescription([]);
        $items = $this->calculator->sortItemsByPriority($items, $address);
        $address->getExtensionAttributes()->setDiscounts([]);
        $addressDiscountAggregator = [];

        /** @var Item $item */
        foreach ($items as $item) {
            if ($item->getNoDiscount() || !$this->calculator->canApplyDiscount($item)) {
                $item->setDiscountAmount(0);
                $item->setBaseDiscountAmount(0);

                // ensure my children are zeroed out
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $child->setDiscountAmount(0);
                        $child->setBaseDiscountAmount(0);
                    }
                }
                continue;
            }
            // to determine the child item discount, we calculate the parent
            if ($item->getParentItem()) {
                continue;
            }

            $eventArgs['item'] = $item;
            $this->eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $this->calculator->process($item);
                foreach ($item->getChildren() as $child) {
                    $eventArgs['item'] = $child;
                    $this->eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);
                    $this->aggregateItemDiscount($child, $total);
                }
            } else {
                $this->calculator->process($item);
                $this->aggregateItemDiscount($item, $total);
            }
            if ($item->getExtensionAttributes()) {
                $this->aggregateDiscountPerRule($item, $address, $addressDiscountAggregator);
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
            $description = $total->getDiscountDescription();
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
     * @param array $addressDiscountAggregator
     * @return void
     */
    private function aggregateDiscountPerRule(
        AbstractItem $item,
        AddressInterface $address,
        array &$addressDiscountAggregator
    ) {
        $discountBreakdown = $item->getExtensionAttributes()->getDiscounts();
        if ($discountBreakdown) {
            foreach ($discountBreakdown as $value) {
                /* @var DiscountDataInterface $discount */
                $discount = $value->getDiscountData();
                $ruleLabel = $value->getRuleLabel();
                $ruleID = $value->getRuleID();
                if (isset($addressDiscountAggregator[$ruleID])) {
                    /** @var RuleDiscount $cartDiscount */
                    $cartDiscount = $addressDiscountAggregator[$ruleID];
                    $discountData = $cartDiscount->getDiscountData();
                    $discountData->setBaseAmount($discountData->getBaseAmount()+$discount->getBaseAmount());
                    $discountData->setAmount($discountData->getAmount()+$discount->getAmount());
                    $discountData->setOriginalAmount($discountData->getOriginalAmount()+$discount->getOriginalAmount());
                    $discountData->setBaseOriginalAmount(
                        $discountData->getBaseOriginalAmount()+$discount->getBaseOriginalAmount()
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
                    $addressDiscountAggregator[$ruleID] = $cartDiscount;
                }
            }
        }
        $address->getExtensionAttributes()->setDiscounts(array_values($addressDiscountAggregator));
    }
}
