<?php
/**
 * Uses ACL to control access. If ACL doesn't contain provided resource,
 * permission for all resources is checked
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Authorization\Policy;

use Magento\Framework\Acl\Builder;
use Magento\Framework\Authorization\PolicyInterface;

class Acl implements PolicyInterface
{
    /**
     * @var \Magento\Framework\Acl\Builder
     */
    protected $_aclBuilder;

    /**
     * @param Builder $aclBuilder
     */
    public function __construct(Builder $aclBuilder)
    {
        $this->_aclBuilder = $aclBuilder;
    }

    /**
     * Check whether given role has access to give id
     *
     * @param string $roleId
     * @param string $resourceId
     * @param string $privilege
     * @return bool
     */
    public function isAllowed($roleId, $resourceId, $privilege = null)
    {
        try {
            return $this->_aclBuilder->getAcl()->isAllowed($roleId, $resourceId, $privilege);
        } catch (\Exception $e) {
            try {
                if (!$this->_aclBuilder->getAcl()->has($resourceId)) {
                    return $this->_aclBuilder->getAcl()->isAllowed($roleId, null, $privilege);
                }
            } catch (\Exception $e) {
            }
        }
        return false;
    }
}
