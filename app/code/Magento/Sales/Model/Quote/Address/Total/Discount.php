<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote\Address\Total;

class Discount extends \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        $quote = $address->getQuote();
        $eventArgs = [
            'website_id' => $this->_storeManager->getStore($quote->getStoreId())->getWebsiteId(),
            'customer_group_id' => $quote->getCustomerGroupId(),
            'coupon_code' => $quote->getCouponCode(),
        ];

        $address->setFreeShipping(0);
        $totalDiscountAmount = 0;
        $subtotalWithDiscount = 0;
        $baseTotalDiscountAmount = 0;
        $baseSubtotalWithDiscount = 0;

        $items = $address->getAllItems();
        if (!count($items)) {
            $address->setDiscountAmount($totalDiscountAmount);
            $address->setSubtotalWithDiscount($subtotalWithDiscount);
            $address->setBaseDiscountAmount($baseTotalDiscountAmount);
            $address->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
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
                /**
                 * Child item discount we calculate for parent
                 */
                if ($item->getParentItemId()) {
                    continue;
                }

                /**
                 * Composite item discount calculation
                 */

                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $eventArgs['item'] = $child;
                        $this->_eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);

                        /**
                         * Parent free shipping we apply to all children
                         */
                        if ($item->getFreeShipping()) {
                            $child->setFreeShipping($item->getFreeShipping());
                        }

                        if (!$child->getDiscountAmount() && $item->getDiscountPercent()) {
                            $child->setDiscountAmount($child->getRowTotal() * $item->getDiscountPercent());
                        }
                        $totalDiscountAmount += $child->getDiscountAmount();
                        //*$item->getQty();
                        $baseTotalDiscountAmount += $child->getBaseDiscountAmount();
                        //*$item->getQty();

                        $child->setRowTotalWithDiscount($child->getRowTotal() - $child->getDiscountAmount());
                        $child->setBaseRowTotalWithDiscount(
                            $child->getBaseRowTotal() - $child->getBaseDiscountAmount()
                        );

                        $subtotalWithDiscount += $child->getRowTotalWithDiscount();
                        $baseSubtotalWithDiscount += $child->getBaseRowTotalWithDiscount();
                    }
                } else {
                    $eventArgs['item'] = $item;
                    $this->_eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);

                    $totalDiscountAmount += $item->getDiscountAmount();
                    $baseTotalDiscountAmount += $item->getBaseDiscountAmount();

                    $item->setRowTotalWithDiscount($item->getRowTotal() - $item->getDiscountAmount());
                    $item->setBaseRowTotalWithDiscount($item->getBaseRowTotal() - $item->getBaseDiscountAmount());

                    $subtotalWithDiscount += $item->getRowTotalWithDiscount();
                    $baseSubtotalWithDiscount += $item->getBaseRowTotalWithDiscount();
                }
            }
        }
        $address->setDiscountAmount($totalDiscountAmount);
        $address->setSubtotalWithDiscount($subtotalWithDiscount);
        $address->setBaseDiscountAmount($baseTotalDiscountAmount);
        $address->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);

        $address->setGrandTotal($address->getGrandTotal() - $address->getDiscountAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() - $address->getBaseDiscountAmount());
        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function fetch(\Magento\Sales\Model\Quote\Address $address)
    {
        $amount = $address->getDiscountAmount();
        if ($amount != 0) {
            $title = __('Discount');
            $code = $address->getCouponCode();
            if (strlen($code)) {
                $title = __('Discount (%1)', $code);
            }
            $address->addTotal(['code' => $this->getCode(), 'title' => $title, 'value' => -$amount]);
        }
        return $this;
    }
}
