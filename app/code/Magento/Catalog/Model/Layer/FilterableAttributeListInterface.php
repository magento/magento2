<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer;

/**
 * Interface \Magento\Catalog\Model\Layer\FilterableAttributeListInterface
 *
 * @since 2.0.0
 */
interface FilterableAttributeListInterface
{
    /**
     * Retrieve list of filterable attributes
     *
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     * @since 2.0.0
     */
    public function getList();
}
