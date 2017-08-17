<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Authorization\RoleLocator;

/**
 * Class \Magento\Framework\Authorization\RoleLocator\DefaultRoleLocator
 *
 */
class DefaultRoleLocator implements \Magento\Framework\Authorization\RoleLocatorInterface
{
    /**
     * Retrieve current role
     *
     * @return string
     */
    public function getAclRoleId()
    {
        return '';
    }
}
