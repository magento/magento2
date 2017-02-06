<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

class RelatedProducts
{
    /**
     * List of related product types
     *
     * @var array
     */
    protected $_relatedProductTypes;

    /**
     * @param array $relatedProductTypes
     */
    public function __construct($relatedProductTypes = [])
    {
        $this->_relatedProductTypes = $relatedProductTypes;
    }

    /**
     * Retrieve Array of product ids which have special relation with products in Cart
     *
     * @param \Magento\Quote\Model\Quote\Item[] $quoteItems
     * @return int[]
     */
    public function getRelatedProductIds(array $quoteItems)
    {
        $productIds = [];
        /** @var $quoteItems \Magento\Quote\Model\Quote\Item[] */
        foreach ($quoteItems as $quoteItem) {
            $productTypeOpt = $quoteItem->getOptionByCode('product_type');
            if ($productTypeOpt instanceof \Magento\Quote\Model\Quote\Item\Option) {
                if (in_array(
                    $productTypeOpt->getValue(),
                    $this->_relatedProductTypes
                ) && $productTypeOpt->getProductId()
                ) {
                    $productIds[] = $productTypeOpt->getProductId();
                }
            }
        }
        return $productIds;
    }
}
