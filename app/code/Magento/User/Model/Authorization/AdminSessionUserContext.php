<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\Model\Auth\Session as AdminSession;

/**
 * Session-based admin user context
 */
class AdminSessionUserContext implements UserContextInterface
{
    /**
     * @var AdminSession
     */
    protected $_adminSession;

    /**
     * Initialize dependencies.
     *
     * @param AdminSession $adminSession
     */
    public function __construct(AdminSession $adminSession)
    {
        $this->_adminSession = $adminSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return $this->_adminSession->hasUser() ? (int)$this->_adminSession->getUser()->getId() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_ADMIN;
    }
}
