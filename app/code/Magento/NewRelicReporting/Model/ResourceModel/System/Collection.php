<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model\ResourceModel\System;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize system updates resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\NewRelicReporting\Model\System', 'Magento\NewRelicReporting\Model\ResourceModel\System');
    }
}
