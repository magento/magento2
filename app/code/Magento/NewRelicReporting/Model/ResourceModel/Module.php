<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model\ResourceModel;

/**
 * Class \Magento\NewRelicReporting\Model\ResourceModel\Module
 *
 * @since 2.0.0
 */
class Module extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize module status resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('reporting_module_status', 'entity_id');
    }
}
