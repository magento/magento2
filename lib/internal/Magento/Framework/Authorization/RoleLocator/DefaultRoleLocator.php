<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Authorization\RoleLocator;

/**
 * Class \Magento\Framework\Authorization\RoleLocator\DefaultRoleLocator
 *
 * @since 2.0.0
 */
class DefaultRoleLocator implements \Magento\Framework\Authorization\RoleLocatorInterface
{
    /**
     * Retrieve current role
     *
     * @return string
     * @since 2.0.0
     */
    public function getAclRoleId()
    {
        return '';
    }
}
