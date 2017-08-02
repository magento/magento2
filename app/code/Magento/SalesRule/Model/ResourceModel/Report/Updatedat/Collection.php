<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Report\Updatedat;

/**
 * Sales report coupons collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends \Magento\SalesRule\Model\ResourceModel\Report\Collection
{
    /**
     * Aggregated Data Table
     *
     * @var string
     * @since 2.0.0
     */
    protected $_aggregationTable = 'salesrule_coupon_aggregated_updated';
}
