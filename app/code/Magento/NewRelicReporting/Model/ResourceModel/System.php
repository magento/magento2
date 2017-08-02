<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model\ResourceModel;

/**
 * Class \Magento\NewRelicReporting\Model\ResourceModel\System
 *
 * @since 2.0.0
 */
class System extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize system updates resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('reporting_system_updates', 'entity_id');
    }
}
