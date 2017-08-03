<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model\ResourceModel\Module;

/**
 * Class \Magento\NewRelicReporting\Model\ResourceModel\Module\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize module status resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\NewRelicReporting\Model\Module::class,
            \Magento\NewRelicReporting\Model\ResourceModel\Module::class
        );
    }
}
