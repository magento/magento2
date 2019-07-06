<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model;

/**
 * Admin User Expiration model.
 * @method string getExpiresAt()
 * @method \Magento\Security\Model\UserExpiration setExpiresAt($value)
 */
class UserExpiration extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Security\Model\ResourceModel\UserExpiration::class);
    }
}
