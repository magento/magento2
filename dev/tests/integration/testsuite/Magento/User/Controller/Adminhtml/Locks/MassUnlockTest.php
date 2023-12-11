<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Locks;

/**
 * Testing unlock controller.
 *
 * @magentoAppArea adminhtml
 */
class MassUnlockTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test index action
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/User/_files/locked_users.php
     */
    public function testMassUnlockAction()
    {
        $userIds = [];
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $model \Magento\User\Model\User */
        $model = $objectManager->create(\Magento\User\Model\User::class);
        $userIds[] = $model->loadByUsername('adminUser1')->getId();
        $userIds[] = $model->loadByUsername('adminUser2')->getId();

        $request = $this->getRequest();
        $request->setPostValue(
            'unlock',
            $userIds
        );
        $this->dispatch('backend/admin/locks/massunlock');

        $this->assertSessionMessages(
            $this->containsEqual((string)__('Unlocked %1 user(s).', count($userIds))),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect();
    }
}
