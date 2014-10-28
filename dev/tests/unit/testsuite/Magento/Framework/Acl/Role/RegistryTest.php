<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Acl\Role;

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
        list($role, ) = $this->initRoles($roleId, $parentRoleId);

        $this->model->addParent($role, 26);
    }
}
