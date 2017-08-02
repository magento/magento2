<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

/**
 * Class \Magento\Quote\Model\Quote\Item\RelatedProducts
 *
 * @since 2.0.0
 */
class RelatedProducts
{
    /**
     * List of related product types
     *
     * @var array
     * @since 2.0.0
     */
    protected $_relatedProductTypes;

    /**
     * @param array $relatedProductTypes
     * @since 2.0.0
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
     * @since 2.0.0
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
