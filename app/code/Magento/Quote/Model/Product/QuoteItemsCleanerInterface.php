<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface \Magento\Quote\Model\Product\QuoteItemsCleanerInterface
 *
 * @api
 */
interface QuoteItemsCleanerInterface
{
    /**
     * @param ProductInterface $product
     * @return void
     */
    public function execute(ProductInterface $product);
}
