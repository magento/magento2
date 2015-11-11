<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model\Resource;

class Orders extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
