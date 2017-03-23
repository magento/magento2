<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;

class QuoteItemQtyList
{
    /**
     * Product qty's checked
     * data is valid if you check quote item qty and use singleton instance
     *
     * @var array
     */
    protected $_checkedQuoteItems = [];

    /**
     * Get product qty includes information from all quote items
     * Need be used only in singleton mode
     *
     * @param int   $productId
     * @param int   $quoteItemId
     * @param int   $quoteId
     * @param float $itemQty
     *
     * @return int
     */
    public function getQty($productId, $quoteItemId, $quoteId, $itemQty)
    {
        $qty = $itemQty;
        if (isset(
            $this->_checkedQuoteItems[$quoteId][$productId]['qty']
        ) && !in_array(
            $quoteItemId,
            $this->_checkedQuoteItems[$quoteId][$productId]['items']
        )
        ) {
            $qty += $this->_checkedQuoteItems[$quoteId][$productId]['qty'];
        }

        $this->_checkedQuoteItems[$quoteId][$productId]['qty'] = $qty;
        $this->_checkedQuoteItems[$quoteId][$productId]['items'][] = $quoteItemId;

        return $qty;
    }
}
