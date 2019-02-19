<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Provide Select object for retrieve product id with minimal price
 */
interface LinkedProductSelectBuilderInterface
{
    /**
     * @param int $productId
     * @return \Magento\Framework\DB\Select[]
     */
    public function build($productId);
}
