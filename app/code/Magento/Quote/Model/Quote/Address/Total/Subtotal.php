<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\Total;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Item;

class Subtotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Sales data
     *
     * @var \Magento\Quote\Model\QuoteValidator
     */
    protected $quoteValidator = null;

    /**
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     */
    public function __construct(\Magento\Quote\Model\QuoteValidator $quoteValidator)
    {
        $this->quoteValidator = $quoteValidator;
    }

    /**
     * Collect address subtotal
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $total->setTotalQty(0);

        $baseVirtualAmount = $virtualAmount = 0;

        $address = $shippingAssignment->getShipping()->getAddress();
        /**
         * Process address items
         */
        $items = $shippingAssignment->getItems();
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

        $total->setBaseVirtualAmount($baseVirtualAmount);
        $total->setVirtualAmount($virtualAmount);

        /**
         * Initialize grand totals
         */
        $this->quoteValidator->validateQuoteAmount($quote, $total->getSubtotal());
        $this->quoteValidator->validateQuoteAmount($quote, $total->getBaseSubtotal());
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

        $quoteItem->setConvertedPrice(null);
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
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => $this->getCode(),
            'title' => $this->getLabel(),
            'value' => $total->getSubtotal()
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Subtotal');
    }
}
