<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\ConfigurableProduct\Block\Stockqty\Type;

use Magento\Catalog\Model\Product;

/**
 * Product stock qty block for configurable product type
 *
 * @api
 * @since 100.0.2
 */
class Configurable extends \Magento\CatalogInventory\Block\Stockqty\Composite
{
    /**
     * Retrieve child products
     *
     * @return Product[]
     */
    protected function _getChildProducts()
    {
        return $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct());
    }
}
