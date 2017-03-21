<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Authorization;

class RoleLocator implements \Magento\Framework\Authorization\RoleLocatorInterface
{
    /**
     * Authentication service
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;

    /**
     * @param \Magento\Backend\Model\Auth\Session $session
     */
    public function __construct(\Magento\Backend\Model\Auth\Session $session)
    {
        $this->_session = $session;
    }

    /**
     * Retrieve current role
     *
     * @return string|null
     */
    public function getAclRoleId()
    {
        if ($this->_session->hasUser()) {
            return $this->_session->getUser()->getAclRole();
        }
        return null;
    }
}
