<?php
/**
 * Test for Mage_Webapi_Model_Resource_Acl_Role
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Resource_Acl_RoleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test for Mage_Webapi_Model_Resource_Acl_Role::getRolesIds()
     *
     * @magentoDataFixture Mage/Webapi/_files/role.php
     * @magentoDataFixture Mage/Webapi/_files/role_with_rule.php
     */
    public function testGetRolesIds()
    {
        $expectedRoleNames = array('test_role', 'Test role');
        /** @var $roleResource Mage_Webapi_Model_Resource_Acl_Role */
        $roleResource = Mage::getResourceModel('Mage_Webapi_Model_Resource_Acl_Role');
        $rolesIds = $roleResource->getRolesIds();
        $this->assertCount(2, $rolesIds);
        foreach ($rolesIds as $roleId) {
            /** @var $role Mage_Webapi_Model_Acl_Role */
            $role = Mage::getModel('Mage_Webapi_Model_Acl_Role')->load($roleId);
            $this->assertNotEmpty($role->getId());
            $this->assertContains($role->getRoleName(), $expectedRoleNames);
        }
    }

    /**
     * Test for Mage_Webapi_Model_Resource_Acl_Role::getRolesList()
     *
     * @magentoDataFixture Mage/Webapi/_files/role.php
     * @magentoDataFixture Mage/Webapi/_files/role_with_rule.php
     */
    public function testGetRolesList()
    {
        /** @var $roleResource Mage_Webapi_Model_Resource_Acl_Role */
        $roleResource = Mage::getResourceModel('Mage_Webapi_Model_Resource_Acl_Role');
        $rolesList = $roleResource->getRolesList();
        $this->assertCount(2, $rolesList);
        foreach ($rolesList as $roleId => $roleName) {
            $role = Mage::getModel('Mage_Webapi_Model_Acl_Role')->load($roleId);
            $this->assertEquals($roleId, $role->getId());
            $this->assertEquals($roleName, $role->getRoleName());
        }
    }

    /**
     * Test for Mage_Webapi_Model_Resource_Acl_Role::_initUniqueFields()
     *
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Role Name already exists.
     * @magentoDataFixture Mage/Webapi/_files/role.php
     */
    public function testInitUniqueFields()
    {
        /** @var $roleResource Mage_Webapi_Model_Resource_Acl_Role */
        $roleResource = Mage::getResourceModel('Mage_Webapi_Model_Resource_Acl_Role');
        $uniqueFields = $roleResource->getUniqueFields();
        $expectedUnique = array(
            array(
                'field' => 'role_name',
                'title' => 'Role Name'
            ),
        );
        $this->assertEquals($expectedUnique, $uniqueFields);

        Mage::getModel('Mage_Webapi_Model_Acl_Role')
            ->setRoleName('test_role')
            ->save();
    }

    /**
     * Test for Mage_Webapi_Model_Resource_Acl_Role::delete()
     *
     * @magentoDataFixture Mage/Webapi/_files/user_with_role.php
     */
    public function testDeleteRole()
    {
        Mage::getModel('Mage_Webapi_Model_Acl_Role')
            ->load('Test role', 'role_name')
            ->delete();
        /** @var Mage_Webapi_Model_Acl_User $user */
        $user = Mage::getModel('Mage_Webapi_Model_Acl_User')
            ->load('test_username', 'api_key');
        $this->assertNotEmpty($user->getId());
    }
}
