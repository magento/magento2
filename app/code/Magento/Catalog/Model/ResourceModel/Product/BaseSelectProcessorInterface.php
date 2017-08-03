<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Framework\DB\Select;

/**
 * Interface BaseSelectProcessorInterface
 * @api
 * @since 2.2.0
 */
interface BaseSelectProcessorInterface
{
    /**
     * Product table alias
     */
    const PRODUCT_TABLE_ALIAS = 'child';

    /**
     * @param Select $select
     * @return Select
     * @since 2.2.0
     */
    public function process(Select $select);
}
