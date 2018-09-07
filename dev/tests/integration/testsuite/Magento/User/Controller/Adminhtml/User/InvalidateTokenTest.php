<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Controller\Adminhtml\User;

use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for Magento\User\Controller\Adminhtml\User\InvalidateToken.
 */
class InvalidateTokenTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testInvalidateSingleToken()
    {
        /** @var \Magento\Integration\Api\AdminTokenServiceInterface $tokenService */
        $tokenService = Bootstrap::getObjectManager()->get(\Magento\Integration\Api\AdminTokenServiceInterface::class);
        /** @var \Magento\Integration\Model\Oauth\Token $tokenModel */
        $tokenModel = Bootstrap::getObjectManager()->get(\Magento\Integration\Model\Oauth\Token::class);
        /** @var \Magento\User\Model\User $userModel */
        $userModel = Bootstrap::getObjectManager()->get(\Magento\User\Model\User::class);

        $adminUserNameFromFixture = 'adminUser';
        $tokenService->createAdminAccessToken(
            $adminUserNameFromFixture,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $adminUserId = $userModel->loadByUsername($adminUserNameFromFixture)->getId();

        // invalidate token
        $this->getRequest()->setParam('user_id', $adminUserId);
        $this->dispatch('backend/admin/user/invalidateToken');
        $token = $tokenModel->loadByAdminId($adminUserId);
        $this->assertEquals(null, $token->getId());
    }

    /**
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testInvalidateMultipleTokens()
    {
        /** @var \Magento\Integration\Api\AdminTokenServiceInterface $tokenService */
        $tokenService = Bootstrap::getObjectManager()->get(\Magento\Integration\Api\AdminTokenServiceInterface::class);

        /** @var \Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory $tokenModelCollectionFactory */
        $tokenModelCollectionFactory = Bootstrap::getObjectManager()->get(
            \Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory::class
        );

        /** @var \Magento\User\Model\User $userModel */
        $userModel = Bootstrap::getObjectManager()->get(\Magento\User\Model\User::class);

        $adminUserNameFromFixture = 'adminUser';
        $tokenService->createAdminAccessToken(
            $adminUserNameFromFixture,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        $tokenService->createAdminAccessToken(
            $adminUserNameFromFixture,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        $adminUserId = $userModel->loadByUsername($adminUserNameFromFixture)->getId();

        // invalidate tokens
        $this->getRequest()->setParam('user_id', $adminUserId);
        $this->dispatch('backend/admin/user/invalidateToken');
        foreach ($tokenModelCollectionFactory->create()->addFilterByAdminId($adminUserId) as $token) {
            $this->assertEquals(1, $token->getRevoked());
        }
    }

    /**
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testInvalidateTokenNoTokens()
    {
        /** @var \Magento\User\Model\User $userModel */
        $userModel = Bootstrap::getObjectManager()->get(\Magento\User\Model\User::class);
        $adminUserNameFromFixture = 'adminUser';
        $adminUserId = $userModel->loadByUsername($adminUserNameFromFixture)->getId();
        // invalidate token
        $this->getRequest()->setParam('user_id', $adminUserId);
        $this->dispatch('backend/admin/user/invalidateToken');
    }

    public function testInvalidateTokenNoUser()
    {
        $this->dispatch('backend/admin/user/invalidateToken');
        $this->assertSessionMessages(
            $this->equalTo(['We can\'t find a user to revoke.']),
            MessageInterface::TYPE_ERROR
        );
    }

    public function testInvalidateTokenInvalidUser()
    {
        $adminUserId = 999;
        // invalidate token
        $this->getRequest()->setParam('user_id', $adminUserId);
        $this->dispatch('backend/admin/user/invalidateToken');
    }
}
