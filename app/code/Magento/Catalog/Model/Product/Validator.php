<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;

class Validator
{
    /**
     * Validate product data
     *
     * @param Product $product
     * @param RequestInterface $request
     * @param \Magento\Framework\Object $response
     * @return array
     */
    public function validate(Product $product, RequestInterface $request, \Magento\Framework\Object $response)
    {
        return $product->validate();
    }
}
