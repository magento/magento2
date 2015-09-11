<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\Total;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
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
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->setCode('discount');
        die('Broken DISCOUNT collector called.');
    }

    /**
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $shippingAssignment->getShipping()->getAddress()->getQuote();
        $eventArgs = [
            'website_id' => $this->storeManager->getStore($quote->getStoreId())->getWebsiteId(),
            'customer_group_id' => $quote->getCustomerGroupId(),
            'coupon_code' => $quote->getCouponCode(),
        ];

        $shippingAssignment->getShipping()->getAddress()->setFreeShipping(0);
        $totalDiscountAmount = 0;
        $subtotalWithDiscount = 0;
        $baseTotalDiscountAmount = 0;
        $baseSubtotalWithDiscount = 0;

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            $total->setDiscountAmount($totalDiscountAmount);
            $total->setSubtotalWithDiscount($subtotalWithDiscount);
            $total->setBaseDiscountAmount($baseTotalDiscountAmount);
            $total->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
            return $this;
        }

        foreach ($items as $item) {
            if ($item->getNoDiscount()) {
                $item->setDiscountAmount(0);
                $item->setBaseDiscountAmount(0);
                $item->setRowTotalWithDiscount($item->getRowTotal());
                $item->setBaseRowTotalWithDiscount($item->getRowTotal());

                $subtotalWithDiscount += $item->getRowTotal();
                $baseSubtotalWithDiscount += $item->getBaseRowTotal();
            } else {
                /** Child item discount we calculate for parent */
                if ($item->getParentItemId()) {
                    continue;
                }

                /** Composite item discount calculation */
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $eventArgs['item'] = $child;
                        $this->eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);

                        /** Parent free shipping we apply to all children */
                        if ($item->getFreeShipping()) {
                            $child->setFreeShipping($item->getFreeShipping());
                        }

                        if (!$child->getDiscountAmount() && $item->getDiscountPercent()) {
                            $child->setDiscountAmount($child->getRowTotal() * $item->getDiscountPercent());
                        }
                        $totalDiscountAmount += $child->getDiscountAmount();
                        $baseTotalDiscountAmount += $child->getBaseDiscountAmount();

                        $child->setRowTotalWithDiscount($child->getRowTotal() - $child->getDiscountAmount());
                        $child->setBaseRowTotalWithDiscount(
                            $child->getBaseRowTotal() - $child->getBaseDiscountAmount()
                        );

                        $subtotalWithDiscount += $child->getRowTotalWithDiscount();
                        $baseSubtotalWithDiscount += $child->getBaseRowTotalWithDiscount();
                    }
                } else {
                    $eventArgs['item'] = $item;
                    $this->eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);

                    $totalDiscountAmount += $item->getDiscountAmount();
                    $baseTotalDiscountAmount += $item->getBaseDiscountAmount();

                    $item->setRowTotalWithDiscount($item->getRowTotal() - $item->getDiscountAmount());
                    $item->setBaseRowTotalWithDiscount($item->getBaseRowTotal() - $item->getBaseDiscountAmount());

                    $subtotalWithDiscount += $item->getRowTotalWithDiscount();
                    $baseSubtotalWithDiscount += $item->getBaseRowTotalWithDiscount();
                }
            }
        }
        $total->setDiscountAmount($totalDiscountAmount);
        $total->setSubtotalWithDiscount($subtotalWithDiscount);
        $total->setBaseDiscountAmount($baseTotalDiscountAmount);
        $total->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
        $total->setGrandTotal($total->getGrandTotal() - $total->getDiscountAmount());
        $total->setBaseGrandTotal($total->getBaseGrandTotal() - $total->getBaseDiscountAmount());
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function fetch(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = [];
        if ($total->getDiscountAmount() != 0) {
            $code = $total->getCouponCode();
            $result = [
                'code' => $this->getCode(),
                'title' => strlen($code) ? __('Discount (%1)', $code) : __('Discount'),
                'value' => -$total->getDiscountAmount()
            ];
        }
        return $result;
    }
}
