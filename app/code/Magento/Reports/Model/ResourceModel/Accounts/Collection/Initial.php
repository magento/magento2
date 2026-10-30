<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Customers by totals Report collection
 */
namespace Magento\Reports\Model\ResourceModel\Accounts\Collection;

/**
 * @api
 * @since 100.0.2
 */
class Initial extends \Magento\Reports\Model\ResourceModel\Report\Collection
{
    /**
     * Report sub-collection class name
     * @var string
     */
    protected $_reportCollection = \Magento\Reports\Model\ResourceModel\Accounts\Collection::class;
}
