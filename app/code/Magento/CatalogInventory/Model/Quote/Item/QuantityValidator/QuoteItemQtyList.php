<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;

/**
 * Class \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList
 *
 * @since 2.0.0
 */
class QuoteItemQtyList
{
    /**
     * Product qty's checked
     * data is valid if you check quote item qty and use singleton instance
     *
     * @var array
     * @since 2.0.0
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
     * @since 2.0.0
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
