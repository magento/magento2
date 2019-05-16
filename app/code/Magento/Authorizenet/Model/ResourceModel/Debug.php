<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Model\ResourceModel;

/**
 * Resource Authorize.net debug model
 * @deprecated 2.2.9 Authorize.net is removing all support for this payment method
 */
class Debug extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('authorizenet_debug', 'debug_id');
    }
}
