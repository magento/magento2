<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 * @since 2.0.0
 */
interface ProductAttributeTypesListInterface
{
    /**
     * Retrieve list of product attribute types
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeTypeInterface[]
     * @since 2.0.0
     */
    public function getItems();
}
