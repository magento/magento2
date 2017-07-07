<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\ResourceModel;

/**
 * Role resource test
 *
 * @magentoAppArea adminhtml
 */
class RoleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRoleUsers()
    {
        $role = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Authorization\Model\Role::class
        );
        $roleResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Authorization\Model\ResourceModel\Role::class
        );

        $this->assertEmpty($roleResource->getRoleUsers($role));

        $role->load(\Magento\TestFramework\Bootstrap::ADMIN_ROLE_NAME, 'role_name');
        $this->assertNotEmpty($roleResource->getRoleUsers($role));
    }
}
