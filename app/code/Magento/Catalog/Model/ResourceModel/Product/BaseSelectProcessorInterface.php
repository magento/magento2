<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Framework\DB\Select;

/**
 * Interface BaseSelectProcessorInterface
 */
interface BaseSelectProcessorInterface
{
    /**
     * Product table alias
     */
    const PRODUCT_RELATION_ALIAS = 'link';

    /**
     * @param Select $select
     * @return Select
     */
    public function process(Select $select);
}
