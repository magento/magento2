<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

interface ProductAttributeTypesListInterface
{
    /**
     * Retrieve list of product attribute types
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeTypeInterface[]
     */
    public function getItems();
}
