<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Authorization;

/**
 * @api
 * @since 100.0.2
 */
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
