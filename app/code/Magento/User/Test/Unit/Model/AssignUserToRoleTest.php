<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Model;

use InvalidArgumentException;
use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\Acl\Role\User as RoleUser;
use Magento\Authorization\Model\ResourceModel\Role as RoleResource;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\User\Model\AssignUserToRole;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssignUserToRoleTest extends TestCase
{
    private const USER_ID = 10;

    /**
     * @var UserResource|MockObject
     */
    private $userResource;

    /**
     * @var RoleResource|MockObject
     */
    private $roleResource;

    /**
     * @var User|MockObject
     */
    private $user;

    /**
     * @var AssignUserToRole
     */
    private $model;

    protected function setUp(): void
    {
        $this->user = $this->createPartialMock(User::class, ['getId', 'getUserName']);
        $userFactory = $this->createMock(UserFactory::class);
        $userFactory->method('create')->willReturn($this->user);
        $roleFactory = $this->createMock(RoleFactory::class);
        $roleFactory->method('create')
            ->willReturnCallback(fn () => $this->createPartialMock(Role::class, ['getId']));
        $this->userResource = $this->createMock(UserResource::class);
        $this->roleResource = $this->createMock(RoleResource::class);
        $this->model = new AssignUserToRole($roleFactory, $userFactory, $this->userResource, $this->roleResource);
    }

    public function testAssignsRoleThroughUserSave(): void
    {
        $this->mockUser(self::USER_ID);
        $this->mockRoles([5 => Group::ROLE_TYPE]);
        $this->userResource->method('getRoles')->with($this->user)->willReturn(['3']);
        $this->userResource->expects($this->once())->method('save')->with($this->user);

        $this->model->execute('admin', 5);

        $this->assertSame(5, $this->user->getRoleId());
    }

    public function testRejectsRoleAlreadyAssignedToAdminUser(): void
    {
        $this->mockUser(self::USER_ID);
        $this->mockRoles([5 => Group::ROLE_TYPE]);
        $this->userResource->method('getRoles')->with($this->user)->willReturn(['5']);
        $this->userResource->expects($this->never())->method('save');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(AssignUserToRole::USER_ALREADY_ASSIGNED_ERROR_MSG, 'admin', 5));

        $this->model->execute('admin', 5);
    }

    public function testRejectsUnknownUser(): void
    {
        $this->mockUser(null);
        $this->userResource->expects($this->never())->method('save');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(AssignUserToRole::USER_DOES_NOT_EXISTS_ERROR_MSG, 'admin'));

        $this->model->execute('admin', 5);
    }

    public function testRejectsUnknownRole(): void
    {
        $this->mockUser(self::USER_ID);
        $this->mockRoles([]);
        $this->userResource->expects($this->never())->method('save');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(AssignUserToRole::ROLE_DOES_NOT_EXISTS_ERROR_MSG, 5));

        $this->model->execute('admin', 5);
    }

    public function testRejectsUserTypeRole(): void
    {
        $this->mockUser(self::USER_ID);
        $this->mockRoles([5 => RoleUser::ROLE_TYPE]);
        $this->userResource->expects($this->never())->method('save');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(AssignUserToRole::ROLE_DOES_NOT_EXISTS_ERROR_MSG, 5));

        $this->model->execute('admin', 5);
    }

    private function mockUser(?int $userId): void
    {
        $this->user->method('getId')->willReturn($userId);
        $this->user->method('getUserName')->willReturn('admin');
        $this->userResource->method('load')->with($this->user, 'admin', 'username')->willReturnSelf();
    }

    /**
     * @param array<int, string> $roleTypes role type per existing role ID
     */
    private function mockRoles(array $roleTypes): void
    {
        $this->roleResource->method('load')
            ->willReturnCallback(function (Role $role, $roleId) use ($roleTypes) {
                $exists = isset($roleTypes[$roleId]);
                $role->method('getId')->willReturn($exists ? $roleId : null);
                $role->setData('role_type', $roleTypes[$roleId] ?? null);
                return $this->roleResource;
            });
    }
}
