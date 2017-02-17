<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
