<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Reports Compared Product Index Resource Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Product\Index\Compared;

/**
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Product\Index\Collection\AbstractCollection
{
    /**
     * Retrieve Product Index table name
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getTableName()
    {
        return $this->getTable('report_compared_product_index');
    }
}
