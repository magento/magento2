<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Authorization;

/**
 * Links Authorization component with application.
 * Responsible for providing the identifier of currently logged in role to \Magento\Framework\Authorization component.
 * Should be implemented by application developer that uses \Magento\Framework\Authorization component.
 *
 * @api
 * @since 2.0.0
 */
interface RoleLocatorInterface
{
    /**
     * Retrieve current role
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getAclRoleId();
}
