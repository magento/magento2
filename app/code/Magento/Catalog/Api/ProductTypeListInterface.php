<?php
/**
 * Product type provider
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 */
interface ProductTypeListInterface
{
    /**
     * Retrieve available product types
     *
     * @return \Magento\Catalog\Api\Data\ProductTypeInterface[]
     */
    public function getProductTypes();
}
