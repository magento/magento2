<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Webapi\Model;

use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Authorization\RoleLocatorInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

class WebapiRoleLocator implements RoleLocatorInterface, ResetAfterRequestInterface
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var RoleCollectionFactory
     */
    protected $roleCollectionFactory;

    /**
     * @var array<string, string|int>
     */
    private array $roleIds = [];

    /**
     * Constructs a role locator using the user context.
     *
     * @param UserContextInterface $userContext
     * @param RoleCollectionFactory $roleCollectionFactory
     */
    public function __construct(
        UserContextInterface $userContext,
        RoleCollectionFactory $roleCollectionFactory
    ) {
        $this->userContext = $userContext;
        $this->roleCollectionFactory = $roleCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getAclRoleId()
    {
        $userId = $this->userContext->getUserId();
        $userType = $this->userContext->getUserType();
        $key = $userType . '|' . $userId;

        if (isset($this->roleIds[$key])) {
            return $this->roleIds[$key];
        }

        $roleCollection = $this->roleCollectionFactory->create();
        /** @var Role $role */
        $role = $roleCollection->setUserFilter($userId, $userType)->getFirstItem();

        if (!$role->getId()) {
            // A missing role is not remembered: it can be created later in the same process
            return null;
        }

        return $this->roleIds[$key] = $role->getId();
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->roleIds = [];
    }
}
