<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;

interface QuoteItemsCleanerInterface
{
    /**
     * @param ProductInterface $product
     * @return void
     */
    public function execute(ProductInterface $product);
}
