<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Reports Viewed Product Index Resource Model
 */
namespace Magento\Reports\Model\ResourceModel\Product\Index;

/**
 * @api
 * @since 100.0.2
 */
class Viewed extends \Magento\Reports\Model\ResourceModel\Product\Index\AbstractIndex
{
    /**
     * Initialize connection and main resource table
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('report_viewed_product_index', 'index_id');
    }
}
