<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Reports Compared Product Index Resource Model
 */
namespace Magento\Reports\Model\ResourceModel\Product\Index;

/**
 * @api
 * @since 100.0.2
 */
class Compared extends \Magento\Reports\Model\ResourceModel\Product\Index\AbstractIndex
{
    /**
     * Initialize connection and main resource table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('report_compared_product_index', 'index_id');
    }
}
