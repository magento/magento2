<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomer\Model\ResourceModel\Login;

/**
 * LoginAsCustomer collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magento\LoginAsCustomer\Model\Login::class, \Magento\LoginAsCustomer\Model\ResourceModel\Login::class);
    }
}
