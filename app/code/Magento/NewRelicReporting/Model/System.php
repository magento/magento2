<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model;

/**
 * Class \Magento\NewRelicReporting\Model\System
 *
 */
class System extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize system updates model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\NewRelicReporting\Model\ResourceModel\System::class);
    }
}
