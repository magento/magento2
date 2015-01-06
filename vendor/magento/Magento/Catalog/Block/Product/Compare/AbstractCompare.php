<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Catalog Compare Products Abstract Block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product\Compare;

abstract class AbstractCompare extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Retrieve Product Compare Helper
     *
     * @return \Magento\Catalog\Helper\Product\Compare
     */
    protected function _getHelper()
    {
        return $this->_compareProduct;
    }

    /**
     * Retrieve Remove Item from Compare List URL
     *
     * @param \Magento\Catalog\Model\Product $item
     * @return string
     */
    public function getRemoveUrl($item)
    {
        return $this->_getHelper()->getRemoveUrl($item);
    }
}
