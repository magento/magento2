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
namespace Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;

class QuoteItemQtyList
{
    /**
     * Product qty's checked
     * data is valid if you check quote item qty and use singleton instance
     *
     * @var array
     */
    protected $_checkedQuoteItems = array();

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
