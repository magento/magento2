<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Framework\Model\AbstractModel;

/**
 * Class \Magento\CatalogUrlRewrite\Model\Category\Product
 *
 * @since 2.0.0
 */
class Product extends AbstractModel
{
    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product::class);
    }
}
