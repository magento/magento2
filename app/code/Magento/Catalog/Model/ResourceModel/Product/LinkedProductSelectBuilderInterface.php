<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
