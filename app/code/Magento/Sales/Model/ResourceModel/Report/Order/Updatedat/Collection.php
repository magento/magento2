<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Order\Updatedat;

/**
 * Report order updated_at collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Report\Order\Collection
{
    /**
     * Aggregated Data Table
     *
     * @var string
     * @since 2.0.0
     */
    protected $_aggregationTable = 'sales_order_aggregated_updated';
}
