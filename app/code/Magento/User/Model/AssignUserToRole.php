<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Model;

use Exception;
use InvalidArgumentException;
use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\ResourceModel\Role as RoleResource;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\User\Model\ResourceModel\User as UserResource;

/**
 * Class AssignUserToRole assigns user to role by username and role id
 */
class AssignUserToRole
{
    public const USER_DOES_NOT_EXISTS_ERROR_MSG = 'There is no user with User name "%s"';
    public const ROLE_DOES_NOT_EXISTS_ERROR_MSG = 'There is no role with Role ID "%s"';
    public const USER_ALREADY_ASSIGNED_ERROR_MSG = 'User with User name %s is already assigned to Role with ID "%s"';
    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @var UserResource
     */
    private $userResource;

    /**
     * @var RoleResource
     */
    private $roleResource;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @param RoleFactory $roleFactory
     * @param UserFactory $userFactory
     * @param UserResource $userResource
     * @param RoleResource $roleResource
     */
    public function __construct(
        RoleFactory $roleFactory,
        UserFactory $userFactory,
        UserResource $userResource,
        RoleResource $roleResource
    ) {
        $this->roleFactory = $roleFactory;
        $this->userResource = $userResource;
        $this->roleResource = $roleResource;
        $this->userFactory = $userFactory;
    }

    /**
     * Assigns user to role
     *
     * @param string $userName
     * @param int $roleId
     *
     * @throws AlreadyExistsException
     * @throws Exception
     */
    public function execute(string $userName, int $roleId): void
    {
        $user = $this->userFactory->create();
        $this->userResource->load($user, $userName, 'username');
        $this->validateUserExists($userName, $user);
        $this->validateParentRoleExists($roleId);

        $userRole = $this->roleFactory->create();
        $this->roleResource->load($userRole, $user->getId(), 'user_id');
        $this->validateUserIsNotAssignedToRole($roleId, $userRole, $user);
        $user->setRoleId($roleId);
        $this->userResource->save($user);
    }

    /**
     * Checks that user exists
     *
     * @param string $userName
     * @param User $user
     *
     * @return void
     * @throws Exception
     */
    private function validateUserExists(string $userName, User $user): void
    {
        if (!$user->getId()) {
            throw new InvalidArgumentException(
                sprintf(self::USER_DOES_NOT_EXISTS_ERROR_MSG, $userName)
            );
        }
    }

    /**
     * Check that Role exists
     *
     * @param int $roleId
     * @throws Exception
     */
    private function validateParentRoleExists(int $roleId): void
    {
        $role = $this->roleFactory->create();
        $this->roleResource->load($role, $roleId);
        if (!$role || !$this->isGroupTypeRole($role)) {
            throw new InvalidArgumentException(
                sprintf(self::ROLE_DOES_NOT_EXISTS_ERROR_MSG, $roleId)
            );
        }
    }

    /**
     * Checks that role is group role
     *
     * @param Role $role
     *
     * @return bool
     */
    private function isGroupTypeRole(Role $role): bool
    {
        return $role->getRoleType() === Group::ROLE_TYPE;
    }

    /**
     * Check user is not assigned to given role
     *
     * @param int $parentRoleId
     * @param Role $role
     * @param User $user
     * @throws Exception
     */
    private function validateUserIsNotAssignedToRole(int $parentRoleId, Role $role, User $user): void
    {
        if ((int)$role->getParentId() === $parentRoleId && (int)$role->getUserId() === (int)$user->getId()) {
            throw new InvalidArgumentException(
                sprintf(self::USER_ALREADY_ASSIGNED_ERROR_MSG, $user->getUsername(), $parentRoleId)
            );
        }
    }
}
