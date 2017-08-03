<?php
/**
 * Magento Authorization component. Can be used to add authorization facility to any application
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Class \Magento\Framework\Authorization
 *
 * @since 2.0.0
 */
class Authorization implements \Magento\Framework\AuthorizationInterface
{
    /**
     * ACL policy
     *
     * @var \Magento\Framework\Authorization\PolicyInterface
     * @since 2.0.0
     */
    protected $_aclPolicy;

    /**
     * ACL role locator
     *
     * @var \Magento\Framework\Authorization\RoleLocatorInterface
     * @since 2.0.0
     */
    protected $_aclRoleLocator;

    /**
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param \Magento\Framework\Authorization\RoleLocatorInterface $roleLocator
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Authorization\PolicyInterface $aclPolicy,
        \Magento\Framework\Authorization\RoleLocatorInterface $roleLocator
    ) {
        $this->_aclPolicy = $aclPolicy;
        $this->_aclRoleLocator = $roleLocator;
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     * @since 2.0.0
     */
    public function isAllowed($resource, $privilege = null)
    {
        return $this->_aclPolicy->isAllowed($this->_aclRoleLocator->getAclRoleId(), $resource, $privilege);
    }
}
