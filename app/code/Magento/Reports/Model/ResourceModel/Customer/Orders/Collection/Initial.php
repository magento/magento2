<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customers by orders Report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Customer\Orders\Collection;

/**
 * @api
 */
class Initial extends \Magento\Reports\Model\ResourceModel\Report\Collection
{
    /**
     * Report sub-collection class name
     * @var string
     */
    protected $_reportCollection = \Magento\Reports\Model\ResourceModel\Customer\Orders\Collection::class;
}
