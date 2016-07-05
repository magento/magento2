<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product\View\AddTo;

/**
 * Product view compare block
 */
class Compare extends \Magento\Catalog\Block\Product\View
{
    /**
     * Return compare params
     *
     * @return string
     */
    public function getPostDataParams()
    {
        $product = $this->getProduct();
        return $this->_compareProduct->getPostDataParams($product);
    }
}
