<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Default authorization policy. Allows access to all resources
 */
namespace Magento\Framework\Authorization\Policy;

class DefaultPolicy implements \Magento\Framework\Authorization\PolicyInterface
{
    /**
     * Check whether given role has access to give id
     *
     * @param string $roleId
     * @param string $resourceId
     * @param string $privilege
     * @return true
     */
    public function isAllowed($roleId, $resourceId, $privilege = null)
    {
        return true;
    }
}
