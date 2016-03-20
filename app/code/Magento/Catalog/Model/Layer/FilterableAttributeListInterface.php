<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer;

interface FilterableAttributeListInterface
{
    /**
     * Retrieve list of filterable attributes
     *
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getList();
}
