<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Authorization\RoleLocator;

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
