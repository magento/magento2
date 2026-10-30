<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\NewRelicReporting\Model;

class Counts extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize counts model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\NewRelicReporting\Model\ResourceModel\Counts::class);
    }
}
