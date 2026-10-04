<?php
/**
 * Copyright 2012 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Authorization\Policy;

use Magento\Framework\Acl\Builder;
use Magento\Framework\Acl\Role\CurrentRoleContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Authorization\PolicyInterface;

/**
 * Uses ACL to control access. If ACL doesn't contain provided resource, permission for all resources is checked.
 */
class Acl implements PolicyInterface
{
    /**
     * @var Builder
     */
    protected $_aclBuilder;

    /**
     * @var CurrentRoleContext
     */
    private $roleContext;

    /**
     * @param Builder $aclBuilder
     * @param ?CurrentRoleContext $roleContext
     */
    public function __construct(Builder $aclBuilder, ?CurrentRoleContext $roleContext = null)
    {
        $this->_aclBuilder = $aclBuilder;
        $this->roleContext = $roleContext ?? ObjectManager::getInstance()->get(CurrentRoleContext::class);
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
        if ($roleId === null || $roleId === '') { //no user is logged in
            return false;
        }
        try {
            $this->roleContext->setRoleId((int) $roleId);
            return $this->_aclBuilder->getAcl()->isAllowed($roleId, $resourceId, $privilege);
        } catch (\Exception $e) {
            try {
                if (!$this->_aclBuilder->getAcl()->hasResource($resourceId)) {
                    return $this->_aclBuilder->getAcl()->isAllowed($roleId, null, $privilege);
                }
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (\Exception $e) {
            }
        } finally {
            $this->roleContext->_resetState();
        }
        return false;
    }
}
