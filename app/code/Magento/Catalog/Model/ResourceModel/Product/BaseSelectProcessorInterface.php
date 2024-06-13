<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Framework\DB\Select;

/**
 * Interface BaseSelectProcessorInterface
 *
 * @api
 * @since 101.0.3
 */
interface BaseSelectProcessorInterface
{
    /**
     * Product table alias
     */
    const PRODUCT_TABLE_ALIAS = 'child';

    /**
     * Process the select statement
     *
     * @param Select $select
     * @return Select
     * @since 101.0.3
     */
    public function process(Select $select);
}
