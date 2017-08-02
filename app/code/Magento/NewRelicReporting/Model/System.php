<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model;

/**
 * Class \Magento\NewRelicReporting\Model\System
 *
 * @since 2.0.0
 */
class System extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize system updates model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\NewRelicReporting\Model\ResourceModel\System::class);
    }
}
