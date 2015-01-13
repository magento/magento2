<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Controller\Adminhtml\User;

use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for Magento\User\Controller\Adminhtml\User\InvalidateToken.
 */
class InvalidateTokenTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testInvalidateSingleToken()
    {
        /** @var \Magento\Integration\Service\V1\AdminTokenService $tokenService */
        $tokenService = Bootstrap::getObjectManager()->get('Magento\Integration\Service\V1\AdminTokenService');
        /** @var \Magento\Integration\Model\Oauth\Token $tokenModel */
        $tokenModel = Bootstrap::getObjectManager()->get('Magento\Integration\Model\Oauth\Token');
        /** @var \Magento\User\Model\User $userModel */
        $userModel = Bootstrap::getObjectManager()->get('Magento\User\Model\User');

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
        $this->assertEquals(1, $token->getRevoked());
    }

    /**
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testInvalidateMultipleTokens()
    {
        /** @var \Magento\Integration\Service\V1\AdminTokenService $tokenService */
        $tokenService = Bootstrap::getObjectManager()->get('Magento\Integration\Service\V1\AdminTokenService');

        /** @var \Magento\Integration\Model\Resource\Oauth\Token\CollectionFactory $tokenModelCollectionFactory */
        $tokenModelCollectionFactory = Bootstrap::getObjectManager()->get(
            'Magento\Integration\Model\Resource\Oauth\Token\CollectionFactory'
        );

        /** @var \Magento\User\Model\User $userModel */
        $userModel = Bootstrap::getObjectManager()->get('Magento\User\Model\User');

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
    public function testInvalidateToken_NoTokens()
    {
        /** @var \Magento\User\Model\User $userModel */
        $userModel = Bootstrap::getObjectManager()->get('Magento\User\Model\User');
        $adminUserNameFromFixture = 'adminUser';
        $adminUserId = $userModel->loadByUsername($adminUserNameFromFixture)->getId();
        // invalidate token
        $this->getRequest()->setParam('user_id', $adminUserId);
        $this->dispatch('backend/admin/user/invalidateToken');
        $this->assertSessionMessages(
            $this->equalTo(['This user has no tokens.']),
            MessageInterface::TYPE_ERROR
        );
    }

    public function testInvalidateToken_NoUser()
    {
        $this->dispatch('backend/admin/user/invalidateToken');
        $this->assertSessionMessages(
            $this->equalTo(['We can\'t find a user to revoke.']),
            MessageInterface::TYPE_ERROR
        );
    }

    public function testInvalidateToken_InvalidUser()
    {
        $adminUserId = 999;
        // invalidate token
        $this->getRequest()->setParam('user_id', $adminUserId);
        $this->dispatch('backend/admin/user/invalidateToken');
        $this->assertSessionMessages(
            $this->equalTo(['This user has no tokens.']),
            MessageInterface::TYPE_ERROR
        );
    }
}
