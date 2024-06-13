<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
     * @param \Magento\Framework\DataObject $response
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(Product $product, RequestInterface $request, \Magento\Framework\DataObject $response)
    {
        return $product->validate();
    }
}
