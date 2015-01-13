<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote\Address\Total;

use Magento\Sales\Model\Quote\Address;
use Magento\Sales\Model\Quote\Address\Item as AddressItem;
use Magento\Sales\Model\Quote\Item;

class Subtotal extends \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData = null;

    /**
     * @param \Magento\Sales\Helper\Data $salesData
     */
    public function __construct(\Magento\Sales\Helper\Data $salesData)
    {
        $this->_salesData = $salesData;
    }

    /**
     * Collect address subtotal
     *
     * @param Address $address
     * @return $this
     */
    public function collect(Address $address)
    {
        parent::collect($address);
        $address->setTotalQty(0);

        $baseVirtualAmount = $virtualAmount = 0;

        /**
         * Process address items
         */
        $items = $this->_getAddressItems($address);
        foreach ($items as $item) {
            if ($this->_initItem($address, $item) && $item->getQty() > 0) {
                /**
                 * Separately calculate subtotal only for virtual products
                 */
                if ($item->getProduct()->isVirtual()) {
                    $virtualAmount += $item->getRowTotal();
                    $baseVirtualAmount += $item->getBaseRowTotal();
                }
            } else {
                $this->_removeItem($address, $item);
            }
        }

        $address->setBaseVirtualAmount($baseVirtualAmount);
        $address->setVirtualAmount($virtualAmount);

        /**
         * Initialize grand totals
         */
        $this->_salesData->checkQuoteAmount($address->getQuote(), $address->getSubtotal());
        $this->_salesData->checkQuoteAmount($address->getQuote(), $address->getBaseSubtotal());
        return $this;
    }

    /**
     * Address item initialization
     *
     * @param Address $address
     * @param AddressItem|Item $item
     * @return bool
     */
    protected function _initItem($address, $item)
    {
        if ($item instanceof AddressItem) {
            $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
        } else {
            $quoteItem = $item;
        }
        $product = $quoteItem->getProduct();
        $product->setCustomerGroupId($quoteItem->getQuote()->getCustomerGroupId());

        /**
         * Quote super mode flag mean what we work with quote without restriction
         */
        if ($item->getQuote()->getIsSuperMode()) {
            if (!$product) {
                return false;
            }
        } else {
            if (!$product || !$product->isVisibleInCatalog()) {
                return false;
            }
        }

        $originalPrice = $product->getPrice();
        if ($quoteItem->getParentItem() && $quoteItem->isChildrenCalculated()) {
            $finalPrice = $quoteItem->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
                $quoteItem->getParentItem()->getProduct(),
                $quoteItem->getParentItem()->getQty(),
                $product,
                $quoteItem->getQty()
            );
            $this->_calculateRowTotal($item, $finalPrice, $originalPrice);
        } elseif (!$quoteItem->getParentItem()) {
            $finalPrice = $product->getFinalPrice($quoteItem->getQty());
            $this->_calculateRowTotal($item, $finalPrice, $originalPrice);
            $this->_addAmount($item->getRowTotal());
            $this->_addBaseAmount($item->getBaseRowTotal());
            $address->setTotalQty($address->getTotalQty() + $item->getQty());
        }

        return true;
    }

    /**
     * Processing calculation of row price for address item
     *
     * @param AddressItem|Item $item
     * @param int $finalPrice
     * @param int $originalPrice
     * @return $this
     */
    protected function _calculateRowTotal($item, $finalPrice, $originalPrice)
    {
        if (!$originalPrice) {
            $originalPrice = $finalPrice;
        }
        $item->setPrice($finalPrice)->setBaseOriginalPrice($originalPrice);
        $item->calcRowTotal();
        return $this;
    }

    /**
     * Remove item
     *
     * @param Address $address
     * @param  AddressItem|Item $item
     * @return $this
     */
    protected function _removeItem($address, $item)
    {
        if ($item instanceof Item) {
            $address->removeItem($item->getId());
            if ($address->getQuote()) {
                $address->getQuote()->removeItem($item->getId());
            }
        } elseif ($item instanceof AddressItem) {
            $address->removeItem($item->getId());
            if ($address->getQuote()) {
                $address->getQuote()->removeItem($item->getQuoteItemId());
            }
        }

        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param Address $address
     * @return $this
     */
    public function fetch(Address $address)
    {
        $address->addTotal(
            ['code' => $this->getCode(), 'title' => __('Subtotal'), 'value' => $address->getSubtotal()]
        );
        return $this;
    }

    /**
     * Get Subtotal label
     *
     * @return string
     */
    public function getLabel()
    {
        return __('Subtotal');
    }
}
