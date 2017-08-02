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

/**
 * Class \Magento\Webapi\Model\WebapiRoleLocator
 *
 * @since 2.0.0
 */
class WebapiRoleLocator implements RoleLocatorInterface
{
    /**
     * @var UserContextInterface
     * @since 2.0.0
     */
    protected $userContext;

    /**
     * @var RoleCollectionFactory
     * @since 2.0.0
     */
    protected $roleCollectionFactory;

    /**
     * Constructs a role locator using the user context.
     *
     * @param UserContextInterface $userContext
     * @param RoleCollectionFactory $roleCollectionFactory
     * @since 2.0.0
     */
    public function __construct(
        UserContextInterface $userContext,
        RoleCollectionFactory $roleCollectionFactory
    ) {
        $this->userContext = $userContext;
        $this->roleCollectionFactory = $roleCollectionFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
