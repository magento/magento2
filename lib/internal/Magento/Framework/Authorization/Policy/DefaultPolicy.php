<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Default authorization policy. Allows access to all resources
 */
namespace Magento\Framework\Authorization\Policy;

/**
 * Class \Magento\Framework\Authorization\Policy\DefaultPolicy
 *
 * @since 2.0.0
 */
class DefaultPolicy implements \Magento\Framework\Authorization\PolicyInterface
{
    /**
     * Check whether given role has access to give id
     *
     * @param string $roleId
     * @param string $resourceId
     * @param string $privilege
     * @return true
     * @since 2.0.0
     */
    public function isAllowed($roleId, $resourceId, $privilege = null)
    {
        return true;
    }
}
