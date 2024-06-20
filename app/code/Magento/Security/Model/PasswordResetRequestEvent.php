<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent as ResourcePasswordResetRequestEvent;

/**
 * PasswordResetRequestEvent Model
 *
 * @method string getAccountReference()
 * @method PasswordResetRequestEvent setAccountReference(string $reference)
 * @method int getRequestType()
 * @method string getCreatedAt()
 * @method PasswordResetRequestEvent setRequestType(int $requestType)
 * @method string getIp()
 * @method PasswordResetRequestEvent setIp(int $ip)
 *
 * @api
 * @since 100.1.0
 */
class PasswordResetRequestEvent extends AbstractModel
{
    /**
     * Type of the event under a security control definition
     */

    /**
     * Customer request a password reset
     */
    const CUSTOMER_PASSWORD_RESET_REQUEST = 0;

    /**
     * Admin User request a password reset
     */
    const ADMIN_PASSWORD_RESET_REQUEST = 1;

    /**
     * Initialize resource model
     *
     * @return void
     * @since 100.1.0
     */
    protected function _construct()
    {
        $this->_init(ResourcePasswordResetRequestEvent::class);
    }
}
