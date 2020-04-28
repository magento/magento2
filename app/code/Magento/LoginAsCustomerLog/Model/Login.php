<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Model;

/**
 * Login model
 */
class Login extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\LoginAsCustomerLog\Model\ResourceModel\Login::class);
    }
}
