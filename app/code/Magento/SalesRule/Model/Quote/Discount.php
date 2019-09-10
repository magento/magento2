<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Quote;

use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\Framework\App\ObjectManager;

/**
 * Discount totals calculation model.
 */
class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    const COLLECTOR_TYPE_CODE = 'discount';

    /**
     * Discount calculation object
     *
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $calculator;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory
     */
    protected $discountFactory;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param DataFactory $discountDataFactory
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        DataFactory $discountDataFactory
    ) {
        $this->setCode(self::COLLECTOR_TYPE_CODE);
        $this->eventManager = $eventManager;
        $this->calculator = $validator;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->discountFactory = $discountDataFactory ?: ObjectManager::getInstance()->get(DataFactory::class);
    }

    /**
     * Collect address discount amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $store = $this->storeManager->getStore($quote->getStoreId());
        $address = $shippingAssignment->getShipping()->getAddress();
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

        /** @var \Magento\Quote\Model\Quote\Item $item */
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
                $this->distributeDiscount($item);
                foreach ($item->getChildren() as $child) {
                    $eventArgs['item'] = $child;
                    $this->eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);
                    $this->aggregateItemDiscount($child, $total);
                }
            } else {
                $this->calculator->process($item);
                $this->aggregateItemDiscount($item, $total);
            }
            $this->aggregateDiscountPerRule($item, $total);
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
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected function aggregateItemDiscount(
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $total->addTotalAmount($this->getCode(), -$item->getDiscountAmount());
        $total->addBaseTotalAmount($this->getCode(), -$item->getBaseDiscountAmount());
        return $this;
    }

    /**
     * Distribute discount at parent item to children items
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    protected function distributeDiscount(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
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
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
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
     * Aggregate Discount per rule
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    private function aggregateDiscountPerRule(
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $discountBreakdown = $item->getDiscountBreakdown();
        foreach ($discountBreakdown as $key => $value) {
            $discountPerRule = $total->getDiscountPerRule() ?? [];
            /**
             * @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discount
             */
            $discount = $value['discount'];
            $rule = $value['rule'];
            if (array_key_exists($key, $discountPerRule)) {
                /**
                 * @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $ruleDiscount
                 */
                $ruleDiscount = $this->discountFactory->create();
                /**
                 * @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
                 */
                $discountData = $discountPerRule[$key]['discount'];
                $ruleDiscount->setAmount($discountData->getAmount()+$discount->getAmount());
                $ruleDiscount->setBaseAmount($discountData->getBaseAmount()+$discount->getBaseAmount());
                $ruleDiscount->setOriginalAmount($discountData->getOriginalAmount()+$discount->getOriginalAmount());
                $ruleDiscount->setBaseOriginalAmount(
                    $discountData->getBaseOriginalAmount()+$discount->getBaseOriginalAmount()
                );
                $discountPerRule[$key]['discount'] = $ruleDiscount;
                $discountPerRule[$key]['rule'] = $rule;
            } else {
                $discountPerRule[$key]['discount'] = $discount;
                $discountPerRule[$key]['rule'] = $rule;
            }
            $total->setDiscountPerRule($discountPerRule);
        }
        return $this;
    }
}
