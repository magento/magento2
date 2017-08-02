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
 * @since 2.0.0
 */
class Tax extends \Magento\Sales\Model\ResourceModel\EntityAbstract
{
    /**
     * Model initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('sales_order_tax', 'tax_id');
    }
}
