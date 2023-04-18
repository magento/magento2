<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model;

use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Authorization\RoleLocatorInterface;

class WebapiRoleLocator implements RoleLocatorInterface
{
    /**
     * Constructs a role locator using the user context.
     *
     * @param UserContextInterface $userContext
     * @param RoleCollectionFactory $roleCollectionFactory
     */
    public function __construct(
        protected readonly UserContextInterface $userContext,
        protected readonly RoleCollectionFactory $roleCollectionFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getAclRoleId()
    {
        $userId = $this->userContext->getUserId();
        $userType = $this->userContext->getUserType();

        $roleCollection = $this->roleCollectionFactory->create();
        /** @var Role $role */
        $role = $roleCollection->setUserFilter($userId, $userType)->getFirstItem();

        if (!$role->getId()) {
            return null;
        }

        return $role->getId();
    }
}
