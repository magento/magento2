<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Authorization;

/**
 * Responsible for internal authorization decision making based on provided role, resource and privilege
 *
 * @api
 * @since 100.0.2
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
