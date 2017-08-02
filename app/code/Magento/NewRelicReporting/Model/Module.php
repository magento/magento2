<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model;

/**
 * Class \Magento\NewRelicReporting\Model\Module
 *
 * @since 2.0.0
 */
class Module extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize module status model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\NewRelicReporting\Model\ResourceModel\Module::class);
    }
}
