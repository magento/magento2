<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Authorization;

/**
 * Responsible for internal authorization decision making based on provided role, resource and privilege
 *
 * @api
 */
interface PolicyInterface
{
    /**
     * Check whether given role has access to given resource
     *
     * @abstract
     * @param string $roleId
     * @param string $resourceId
     * @param string|null $privilege
     * @return bool
     */
    public function isAllowed($roleId, $resourceId, $privilege = null);
}
