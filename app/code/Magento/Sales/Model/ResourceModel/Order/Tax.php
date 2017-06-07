<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order;

/**
 * Order Tax Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tax extends \Magento\Sales\Model\ResourceModel\EntityAbstract
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_tax', 'tax_id');
    }
}
