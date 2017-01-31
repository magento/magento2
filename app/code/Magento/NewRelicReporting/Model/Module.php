<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->_init('Magento\NewRelicReporting\Model\ResourceModel\Module');
    }
}
