<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\ResourceModel\Report\Settlement;

/**
 * Report settlement row resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Row extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource model initialization.
     * Set main entity table name and primary key field name.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('paypal_settlement_report_row', 'row_id');
    }
}
