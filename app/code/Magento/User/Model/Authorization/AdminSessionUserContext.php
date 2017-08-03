<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\Model\Auth\Session as AdminSession;

/**
 * Session-based admin user context
 * @since 2.0.0
 */
class AdminSessionUserContext implements UserContextInterface
{
    /**
     * @var AdminSession
     * @since 2.0.0
     */
    protected $_adminSession;

    /**
     * Initialize dependencies.
     *
     * @param AdminSession $adminSession
     * @since 2.0.0
     */
    public function __construct(AdminSession $adminSession)
    {
        $this->_adminSession = $adminSession;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUserId()
    {
        return $this->_adminSession->hasUser() ? (int)$this->_adminSession->getUser()->getId() : null;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_ADMIN;
    }
}
