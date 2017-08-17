<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model;

/**
 * Class \Magento\NewRelicReporting\Model\Module
 *
 */
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
