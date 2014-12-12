<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Layer;

interface StateKeyInterface
{
    /**
     * Build state key
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function toString($category);
}
