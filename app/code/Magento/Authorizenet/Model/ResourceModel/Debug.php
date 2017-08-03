<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model\ResourceModel;

/**
 * Resource Authorize.net debug model
 * @since 2.0.0
 */
class Debug extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('authorizenet_debug', 'debug_id');
    }
}
