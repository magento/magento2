<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Report Reviews collection
 */
namespace Magento\Reports\Model\ResourceModel\Product\Sold\Collection;

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
    protected $_reportCollection = \Magento\Reports\Model\ResourceModel\Product\Sold\Collection::class;
}
