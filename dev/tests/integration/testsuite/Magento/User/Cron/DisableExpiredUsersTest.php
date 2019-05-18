<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Cron;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class DisableExpiredUsersTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @magentoDataFixture Magento/User/_files/expired_users.php
     */
    public function testExecuteWithExpiredUser()
    {
        $adminUserNameFromFixture = 'adminUser3';

        $tokenService = Bootstrap::getObjectManager()->get(\Magento\Integration\Api\AdminTokenServiceInterface::class);
        $tokenService->createAdminAccessToken(
            $adminUserNameFromFixture,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        /** @var \Magento\User\Cron\DisableExpiredUsers $job */
        $job = Bootstrap::getObjectManager()->create(\Magento\User\Cron\DisableExpiredUsers::class);
        $job->execute();

        /** @var \Magento\User\Model\User $user */
        $user = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $user->loadByUsername($adminUserNameFromFixture);

        /** @var \Magento\Integration\Model\Oauth\Token $tokenModel */
        $tokenModel = Bootstrap::getObjectManager()->get(\Magento\Integration\Model\Oauth\Token::class);
        $token = $tokenModel->loadByAdminId($user->getId());

        static::assertEquals(0, $user->getIsActive());
        static::assertNull($user->getExpiresAt());
        static::assertEquals(null, $token->getId());
    }

    /**
     * @magentoDataFixture Magento/User/_files/expired_users.php
     */
    public function testExecuteWithNonExpiredUser()
    {
        $adminUserNameFromFixture = 'adminUser4';

        $tokenService = Bootstrap::getObjectManager()->get(\Magento\Integration\Api\AdminTokenServiceInterface::class);
        $tokenService->createAdminAccessToken(
            $adminUserNameFromFixture,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        /** @var \Magento\User\Cron\DisableExpiredUsers $job */
        $job = Bootstrap::getObjectManager()->create(\Magento\User\Cron\DisableExpiredUsers::class);
        $job->execute();

        /** @var \Magento\User\Model\User $user */
        $user = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $user->loadByUsername($adminUserNameFromFixture);

        /** @var \Magento\Integration\Model\Oauth\Token $tokenModel */
        $tokenModel = Bootstrap::getObjectManager()->get(\Magento\Integration\Model\Oauth\Token::class);
        $token = $tokenModel->loadByAdminId($user->getId());

        static::assertEquals(1, $user->getIsActive());
        static::assertNotNull($user->getExpiresAt());
        static::assertNotNull($token->getId());

    }

}
