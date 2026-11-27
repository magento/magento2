<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Model;

use InvalidArgumentException;
use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\ResourceModel\Role as RoleResource;
use Magento\Authorization\Model\RoleFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\User\Model\ResourceModel\User as UserResource;

/**
 * Assigns an admin user to an admin role group by username and role ID
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
     * @return void
     * @throws InvalidArgumentException
     * @throws AlreadyExistsException
     */
    public function execute(string $userName, int $roleId): void
    {
        $user = $this->userFactory->create();
        $this->userResource->load($user, $userName, 'username');
        if (!$user->getId()) {
            throw new InvalidArgumentException(sprintf(self::USER_DOES_NOT_EXISTS_ERROR_MSG, $userName));
        }
        $this->validateParentRoleExists($roleId);

        $assignedRoleIds = array_map('intval', $this->userResource->getRoles($user));
        if (in_array($roleId, $assignedRoleIds, true)) {
            throw new InvalidArgumentException(
                sprintf(self::USER_ALREADY_ASSIGNED_ERROR_MSG, $user->getUserName(), $roleId)
            );
        }

        $user->setRoleId($roleId);
        $this->userResource->save($user);
    }

    /**
     * Check that the role exists and is a role group
     *
     * @param int $roleId
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateParentRoleExists(int $roleId): void
    {
        $role = $this->roleFactory->create();
        $this->roleResource->load($role, $roleId);
        if (!$role->getId() || $role->getRoleType() !== Group::ROLE_TYPE) {
            throw new InvalidArgumentException(sprintf(self::ROLE_DOES_NOT_EXISTS_ERROR_MSG, $roleId));
        }
    }
}
