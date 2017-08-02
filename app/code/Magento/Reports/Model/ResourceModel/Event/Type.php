<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report event type resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Event;

/**
 * @api
 * @since 2.0.0
 */
class Type extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Main table initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('report_event_types', 'event_type_id');
    }
}
