<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model;

class Users extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize users model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\NewRelicReporting\Model\ResourceModel\Users::class);
    }
}
