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
    public function testExecute()
    {
        /** @var \Magento\User\Cron\DisableExpiredUsers $job */
        $job = Bootstrap::getObjectManager()->create(\Magento\User\Cron\DisableExpiredUsers::class);
        $job->execute();

        /** @var \Magento\User\Model\User $user */
        $user = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $user->loadByUsername('adminUser3');
        static::assertEquals(0, $user->getIsActive());
        static::assertNull($user->getExpiresAt());
    }

}
