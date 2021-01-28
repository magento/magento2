<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit\Role;

use \Magento\Framework\Acl\Role\Registry;

class RegistryTest extends \PHPUnit\Framework\TestCase
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
     * @throws \Zend_Acl_Role_Registry_Exception
     */
    protected function initRoles($roleId, $parentRoleId)
    {
        $parentRole = $this->createMock(\Zend_Acl_Role_Interface::class);
        $parentRole->expects($this->any())->method('getRoleId')->willReturn($parentRoleId);

        $role = $this->createMock(\Zend_Acl_Role_Interface::class);
        $role->expects($this->any())->method('getRoleId')->willReturn($roleId);

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

    /**
     */
    public function testAddParentWrongChildId()
    {
        $this->expectException(\Zend_Acl_Role_Registry_Exception::class);
        $this->expectExceptionMessage('Child Role id \'20\' does not exist');

        $roleId = 1;
        $parentRoleId = 2;
        list(, $parentRole) = $this->initRoles($roleId, $parentRoleId);

        $this->model->addParent(20, $parentRole);
    }

    /**
     */
    public function testAddParentWrongParentId()
    {
        $this->expectException(\Zend_Acl_Role_Registry_Exception::class);
        $this->expectExceptionMessage('Parent Role id \'26\' does not exist');

        $roleId = 1;
        $parentRoleId = 2;
        list($role,) = $this->initRoles($roleId, $parentRoleId);

        $this->model->addParent($role, 26);
    }
}
