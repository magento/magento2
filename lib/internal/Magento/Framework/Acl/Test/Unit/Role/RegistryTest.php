<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Acl\Test\Unit\Role;

use Laminas\Permissions\Acl\Exception\InvalidArgumentException;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Magento\Framework\Acl\Role\Registry;
use PHPUnit\Framework\TestCase;

class RegistryTest extends TestCase
{
    /**
     * @var Registry
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new Registry();
    }

    /**
     * @param $roleId
     * @param $parentRoleId
     * @return array
     * @throws InvalidArgumentException
     */
    protected function initRoles($roleId, $parentRoleId)
    {
        $parentRole = $this->createMock(RoleInterface::class);
        $parentRole->method('getRoleId')->willReturn($parentRoleId);

        $role = $this->createMock(RoleInterface::class);
        $role->method('getRoleId')->willReturn($roleId);

        $this->model->add($role);
        $this->model->add($parentRole);
        return [$role, $parentRole];
    }

    public function testAddParent()
    {
        $roleId = 1;
        $parentRoleId = 2;
        list($role, $parentRole) = $this->initRoles($roleId, $parentRoleId);

        $this->assertEmpty($this->model->getParents($roleId));
        $this->model->addParent($role, $parentRole);
        $this->model->getParents($roleId);
        $this->assertEquals([$parentRoleId => $parentRole], $this->model->getParents($roleId));
    }

    public function testAddParentByIds()
    {
        $roleId = 14;
        $parentRoleId = 25;
        list(, $parentRole) = $this->initRoles($roleId, $parentRoleId);

        $this->assertEmpty($this->model->getParents($roleId));
        $this->model->addParent($roleId, $parentRoleId);
        $this->model->getParents($roleId);
        $this->assertEquals([$parentRoleId => $parentRole], $this->model->getParents($roleId));
    }

    public function testAddParentWrongChildId()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Child Role id \'20\' does not exist');
        $roleId = 1;
        $parentRoleId = 2;
        list(, $parentRole) = $this->initRoles($roleId, $parentRoleId);

        $this->model->addParent(20, $parentRole);
    }

    public function testAddParentWrongParentId()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parent Role id \'26\' does not exist');
        $roleId = 1;
        $parentRoleId = 2;
        list($role, ) = $this->initRoles($roleId, $parentRoleId);

        $this->model->addParent($role, 26);
    }
}
