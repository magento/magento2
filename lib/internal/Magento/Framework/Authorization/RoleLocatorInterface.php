<?php
/**
 * Links Authorization component with application.
 * Responsible for providing the identifier of currently logged in role to \Magento\Framework\Authorization component.
 * Should be implemented by application developer that uses \Magento\Framework\Authorization component.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Authorization;

interface RoleLocatorInterface
{
    /**
     * Retrieve current role
     *
     * @return string|null
     */
    public function getAclRoleId();
}
