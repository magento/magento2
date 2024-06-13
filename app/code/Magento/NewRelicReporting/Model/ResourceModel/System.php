<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\NewRelicReporting\Model\ResourceModel;

class System extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize system updates resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('reporting_system_updates', 'entity_id');
    }
}
