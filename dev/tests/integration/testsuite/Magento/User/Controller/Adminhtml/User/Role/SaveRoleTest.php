<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\User\Controller\Adminhtml\User\Role;

use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Controller\Adminhtml\User\Role\SaveRole;

/**
 * Test class for \Magento\User\Controller\Adminhtml\User\Role\SaveRole.
 *
 * @magentoAppArea adminhtml
 */
class SaveRoleTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test execute method for different scopes
     *
     * @magentoDataFixture Magento/User/_files/user_with_custom_role.php
     * @magentoDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     */
    public function testExecuteWithDifferentScopes()
    {
        $objectManager = Bootstrap::getObjectManager();
        $store = $objectManager->get(Store::class);
        $store->load('third_store_view', 'code');

        /** @var \Magento\Authorization\Model\RoleFactory $roleFactory */
        $roleFactory = $objectManager->create(\Magento\Authorization\Model\RoleFactory::class);
        $role = $roleFactory->create()->load('test_custom_role', 'role_name');
        $roleId = $role->getId();

        $post = [
            'role_id' => $roleId,
            'in_role_user_old'=> '',
            'in_role_user'=> '',
            'all' => 1,
            'current_password' => 'password1',
            'rolename' => $role->getRoleName(),
            'gws_is_all' => 0,
            'gws_websites' => [1, (int)$store->getWebsiteId()],
        ];

        $this->getRequest()->setPostValue($post);

        $model = $objectManager->create(SaveRole::class);
        $model->execute();

        /** @var \Magento\Authorization\Model\RoleFactory $roleFactory */
        $roleFactory = $objectManager->create(\Magento\Authorization\Model\RoleFactory::class);
        $role = $roleFactory->create()->load($roleId);
        $this->assertEquals(2, count($role->getGwsWebsites()));

        $post = [
            'role_id' => $roleId,
            'in_role_user_old'=> '',
            'in_role_user'=> '',
            'all' => 1,
            'current_password' => 'password1',
            'rolename' => $role->getRoleName(),
            'gws_is_all' => 1,
        ];

        $this->getRequest()->setPostValue($post);

        $registry = $objectManager->get(Registry::class);
        $registry->unregister('current_role');

        $model = $objectManager->create(SaveRole::class);
        $model->execute();

        /** @var \Magento\Authorization\Model\RoleFactory $roleFactory */
        $roleFactory = $objectManager->create(\Magento\Authorization\Model\RoleFactory::class);
        $role = $roleFactory->create()->load($roleId);
        $this->assertNull($role->getGwsWebsites());
    }
}
