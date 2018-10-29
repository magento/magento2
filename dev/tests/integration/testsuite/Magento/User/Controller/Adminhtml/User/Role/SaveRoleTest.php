<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\User\Controller\Adminhtml\User\Role;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Message\MessageInterface;
use Magento\User\Controller\Adminhtml\User\Role\SaveRole;

/**
 * Test class for \Magento\User\Controller\Adminhtml\User\Role.
 *
 * @magentoAppArea adminhtml
 */
class SaveRoleTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test execute method
     *
     * @magentoDataFixture Magento/User/_files/two_users_with_role.php
     * @magentoDbIsolation disabled
     */
    public function testExecute()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\User\Model\User $currentAdmin */
        $currentAdmin = $objectManager->create(User::class)
            ->loadByUsername('user');
        /** @var \Magento\Backend\Model\Auth\Session $authSession */
        $authSession = $objectManager->create(Session::class);
        $authSession->setUser($currentAdmin);
        $user1Id = $objectManager->create(User::class)
            ->loadByUsername('johnAdmin')->getId();
        $user2Id = $objectManager->create(User::class)
            ->loadByUsername('annAdmin')->getId();

        /** @var \Magento\Authorization\Model\RoleFactory $roleFactory */
        $roleFactory = $objectManager->create(\Magento\Authorization\Model\RoleFactory::class);
        $role = $roleFactory->create()->load(1);

        /** @var \Magento\AdminGws\Model\Role $gwsRole */
        $gwsRole = $objectManager->get(\Magento\AdminGws\Model\Role::class);
        $gwsRole->setAdminRole($role);
        $gwsRole->setStoreGroupIds([1]);

        $params = [
            'role_id' => 1,
            'in_role_user_old'=> $user1Id . '=true&' . $user2Id . '=true',
            'in_role_user'=> $user1Id . '=true&' . $user2Id . '=true',
            'all' => 1,
            'current_password' => 'password1',
            'rolename' => 'Administrators',
        ];

        $post = [
            'gws_is_all' => 1,
            'gws_store_groups' => ['1'],
        ];

        $this->getRequest()->setParams($params);
        $this->getRequest()->setPostValue($post);

        $model = $objectManager->create(SaveRole::class);
        $model->execute();
        $this->assertSessionMessages(
            $this->equalTo(['You saved the role.']),
            MessageInterface::TYPE_SUCCESS
        );
    }
}
