<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit\Role;

use \Magento\Framework\Acl\Role\Registry;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Registry
     */
    protected $model;

    protected function setUp()
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
        $parentRole = $this->getMock('Zend_Acl_Role_Interface');
        $parentRole->expects($this->any())->method('getRoleId')->will($this->returnValue($parentRoleId));

        $role = $this->getMock('Zend_Acl_Role_Interface');
        $role->expects($this->any())->method('getRoleId')->will($this->returnValue($roleId));

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
     * @expectedException \Zend_Acl_Role_Registry_Exception
     * @expectedExceptionMessage Child Role id '20' does not exist
     */
    public function testAddParentWrongChildId()
    {
        $roleId = 1;
        $parentRoleId = 2;
        list(, $parentRole) = $this->initRoles($roleId, $parentRoleId);

        $this->model->addParent(20, $parentRole);
    }

    /**
     * @expectedException \Zend_Acl_Role_Registry_Exception
     * @expectedExceptionMessage Parent Role id '26' does not exist
     */
    public function testAddParentWrongParentId()
    {
        $roleId = 1;
        $parentRoleId = 2;
        list($role,) = $this->initRoles($roleId, $parentRoleId);

        $this->model->addParent($role, 26);
    }
}
