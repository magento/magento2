<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\ResourceModel\Sales\Order;

/**
 * Sales order tax resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tax extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_tax', 'tax_id');
    }
}
