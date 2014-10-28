<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\StoreManagerInterface $storeManager
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
        $eventArgs = array(
            'website_id' => $this->_storeManager->getStore($quote->getStoreId())->getWebsiteId(),
            'customer_group_id' => $quote->getCustomerGroupId(),
            'coupon_code' => $quote->getCouponCode()
        );

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

                        /**
                         * @todo Parent discount we apply for all children without discount
                         */
                        if (!$child->getDiscountAmount() && $item->getDiscountPercent()) {
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
            $address->addTotal(array('code' => $this->getCode(), 'title' => $title, 'value' => -$amount));
        }
        return $this;
    }
}
