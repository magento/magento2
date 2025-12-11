<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Provide Select object for retrieve product id with minimal price
 *
 * @api
 */
interface LinkedProductSelectBuilderInterface
{
    /**
     * Build Select objects
     *
     * @param int $productId
     * @param int $storeId
     * @return \Magento\Framework\DB\Select[]
     */
    public function build(int $productId, int $storeId) : array;
}
