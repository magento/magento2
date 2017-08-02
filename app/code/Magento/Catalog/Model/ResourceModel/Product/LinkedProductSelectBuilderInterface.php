<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Provide Select object for retrieve product id with minimal price
 * @since 2.2.0
 */
interface LinkedProductSelectBuilderInterface
{
    /**
     * @param int $productId
     * @return \Magento\Framework\DB\Select[]
     * @since 2.2.0
     */
    public function build($productId);
}
