<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Framework\Model\AbstractModel;

class Product extends AbstractModel
{
    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\CatalogUrlRewrite\Model\Resource\Category\Product');
    }
}
