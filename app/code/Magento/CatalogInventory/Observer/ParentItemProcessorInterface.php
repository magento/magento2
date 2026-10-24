<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Observer;

use Magento\Catalog\Api\Data\ProductInterface as Product;

/**
 * Interface for processing parent items of complex product types
 */
interface ParentItemProcessorInterface
{
    /**
     * Process stock for parent items
     *
     * @param Product $product
     * @return void
     */
    public function process(Product $product);
}
