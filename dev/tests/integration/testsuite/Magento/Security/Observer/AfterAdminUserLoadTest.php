<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Observer;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\Security\Observer\AfterAdminUserLoad
 *
 * @magentoAppArea adminhtml
 */
class AfterAdminUserLoadTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testWithUserWithExpiration()
    {
        $adminUserNameFromFixture = 'adminUserExpired';
        /** @var \Magento\User\Model\User $user */
        $user = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $user->loadByUsername($adminUserNameFromFixture);
        $userId = $user->getId();
        $loadedUser = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $loadedUser->load($userId);
        static::assertNotNull($loadedUser->getExpiresAt());
    }

    /**
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testWithNonExpiredUser()
    {
        $adminUserNameFromFixture = 'dummy_username';
        $user = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $user->loadByUsername($adminUserNameFromFixture);
        $userId = $user->getId();
        $loadedUser = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $loadedUser->load($userId);
        static::assertNull($loadedUser->getExpiresAt());
    }

}
