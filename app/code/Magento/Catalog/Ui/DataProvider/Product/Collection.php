<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

/**
 * Collection which is used for rendering product list in the backend.
 *
 * Used for product grid and customizes behavior of the default Product collection for grid needs.
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Join Product Price Table using left-join
     *
     * Customizes how price index joined to product collection in order to show disabled products in the list.
     * It's needed as price index by its nature contains only values for enabled products.
     * However, product grid requires to show all available products without any implicit filtering.
     *
     * @codeCoverageIgnore
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _productLimitationJoinPrice()
    {
        return $this->_productLimitationPrice(true);
    }
}
