<?php
/**
 * Test for \Magento\Webapi\Model\Resource\Acl\Role.
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Resource\Acl;

class RoleTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Test for \Magento\Webapi\Model\Resource\Acl\Role::getRolesIds().
     *
     * @magentoDataFixture Magento/Webapi/_files/role.php
     * @magentoDataFixture Magento/Webapi/_files/role_with_rule.php
     */
    public function testGetRolesIds()
    {
        $expectedRoleNames = array('test_role', 'Test role');
        /** @var $roleResource \Magento\Webapi\Model\Resource\Acl\Role */
        $roleResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Resource\Acl\Role');
        $rolesIds = $roleResource->getRolesIds();
        $this->assertCount(2, $rolesIds);
        foreach ($rolesIds as $roleId) {
            /** @var $role \Magento\Webapi\Model\Acl\Role */
            $role = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Acl\Role')->load($roleId);
            $this->assertNotEmpty($role->getId());
            $this->assertContains($role->getRoleName(), $expectedRoleNames);
        }
    }

    /**
     * Test for \Magento\Webapi\Model\Resource\Acl\Role::getRolesList().
     *
     * @magentoDataFixture Magento/Webapi/_files/role.php
     * @magentoDataFixture Magento/Webapi/_files/role_with_rule.php
     */
    public function testGetRolesList()
    {
        /** @var $roleResource \Magento\Webapi\Model\Resource\Acl\Role */
        $roleResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Resource\Acl\Role');
        $rolesList = $roleResource->getRolesList();
        $this->assertCount(2, $rolesList);
        foreach ($rolesList as $roleId => $roleName) {
            $role = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Acl\Role')->load($roleId);
            $this->assertEquals($roleId, $role->getId());
            $this->assertEquals($roleName, $role->getRoleName());
        }
    }

    /**
     * Test for \Magento\Webapi\Model\Resource\Acl\Role::_initUniqueFields().
     *
     * @expectedException \Magento\Core\Exception
     * @expectedExceptionMessage Role Name already exists.
     * @magentoDataFixture Magento/Webapi/_files/role.php
     */
    public function testInitUniqueFields()
    {
        /** @var $roleResource \Magento\Webapi\Model\Resource\Acl\Role */
        $roleResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Resource\Acl\Role');
        $uniqueFields = $roleResource->getUniqueFields();
        $expectedUnique = array(
            array(
                'field' => 'role_name',
                'title' => 'Role Name'
            ),
        );
        $this->assertEquals($expectedUnique, $uniqueFields);

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Acl\Role')
            ->setRoleName('test_role')
            ->save();
    }

    /**
     * Test for \Magento\Webapi\Model\Resource\Acl\Role::delete().
     *
     * @magentoDataFixture Magento/Webapi/_files/user_with_role.php
     */
    public function testDeleteRole()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Acl\Role')
            ->load('Test role', 'role_name')
            ->delete();
        /** @var \Magento\Webapi\Model\Acl\User $user */
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Acl\User')
            ->load('test_username', 'api_key');
        $this->assertNotEmpty($user->getId());
    }
}
