<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Block\Stockqty;

use Magento\Catalog\Model\Product;

/**
 * Product stock qty block for abstract composite product
 * @since 2.0.0
 */
abstract class Composite extends DefaultStockqty
{
    /**
     * Child products cache
     *
     * @var Product[]
     * @since 2.0.0
     */
    private $_childProducts;

    /**
     * Retrieve child products
     *
     * @return Product[]
     * @since 2.0.0
     */
    abstract protected function _getChildProducts();

    /**
     * Retrieve child products (using cache)
     *
     * @return Product[]
     * @since 2.0.0
     */
    public function getChildProducts()
    {
        if ($this->_childProducts === null) {
            $this->_childProducts = $this->_getChildProducts();
        }
        return $this->_childProducts;
    }

    /**
     * Retrieve id of details table placeholder in template
     *
     * @return string
     * @since 2.0.0
     */
    public function getDetailsPlaceholderId()
    {
        return $this->getPlaceholderId() . '-details';
    }
}
