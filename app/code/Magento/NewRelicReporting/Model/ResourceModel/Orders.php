<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\NewRelicReporting\Model\ResourceModel;

class Orders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize orders resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('reporting_orders', 'entity_id');
    }
}
