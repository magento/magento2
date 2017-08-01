<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model\ResourceModel\System;

/**
 * Class \Magento\NewRelicReporting\Model\ResourceModel\System\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize system updates resource collection
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\NewRelicReporting\Model\System::class,
            \Magento\NewRelicReporting\Model\ResourceModel\System::class
        );
    }
}
