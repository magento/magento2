<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model\ResourceModel\Report\Updatedat;

/**
 * Class \Magento\Tax\Model\ResourceModel\Report\Updatedat\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Tax\Model\ResourceModel\Report\Collection
{
    /**
     * Aggregated Data Table
     *
     * @var string
     * @since 2.0.0
     */
    protected $_aggregationTable = 'tax_order_aggregated_updated';
}
