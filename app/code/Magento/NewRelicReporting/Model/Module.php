<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\NewRelicReporting\Model;

class Module extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize module status model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\NewRelicReporting\Model\ResourceModel\Module::class);
    }
}
