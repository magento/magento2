<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model\ResourceModel\Counts;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize counts resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\NewRelicReporting\Model\Counts', 'Magento\NewRelicReporting\Model\ResourceModel\Counts');
    }
}
