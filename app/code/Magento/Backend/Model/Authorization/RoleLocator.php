<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Authorization;

/**
 * @api
 * @since 2.0.0
 */
class RoleLocator implements \Magento\Framework\Authorization\RoleLocatorInterface
{
    /**
     * Authentication service
     *
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $_session;

    /**
     * @param \Magento\Backend\Model\Auth\Session $session
     * @since 2.0.0
     */
    public function __construct(\Magento\Backend\Model\Auth\Session $session)
    {
        $this->_session = $session;
    }

    /**
     * Retrieve current role
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getAclRoleId()
    {
        if ($this->_session->hasUser()) {
            return $this->_session->getUser()->getAclRole();
        }
        return null;
    }
}
