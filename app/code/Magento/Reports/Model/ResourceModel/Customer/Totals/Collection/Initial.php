<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customers New Accounts Report collection
 */
namespace Magento\Reports\Model\ResourceModel\Customer\Totals\Collection;

/**
 * @api
 * @since 100.0.2
 */
class Initial extends \Magento\Reports\Model\ResourceModel\Report\Collection
{
    /**
     * Report sub-collection class name
     *
     * @var string
     */
    protected $_reportCollection = \Magento\Reports\Model\ResourceModel\Customer\Totals\Collection::class;
}
