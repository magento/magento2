<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model\ResourceModel;

/**
 * Class \Magento\NewRelicReporting\Model\ResourceModel\Orders
 *
 * @since 2.0.0
 */
class Orders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize orders resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('reporting_orders', 'entity_id');
    }
}
