<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Model\ResourceModel\Login;

/**
 * LoginAsCustomerLog collection
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
        $this->_init(
            \Magento\LoginAsCustomerLog\Model\Login::class,
            \Magento\LoginAsCustomerLog\Model\ResourceModel\Login::class
        );
    }
}
