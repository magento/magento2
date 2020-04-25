<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerUi\Model\ResourceModel\Login;

/**
 * LoginAsCustomerUi collection
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
        $this->_init(\Magento\LoginAsCustomerUi\Model\Login::class, \Magento\LoginAsCustomerUi\Model\ResourceModel\Login::class);
    }
}
