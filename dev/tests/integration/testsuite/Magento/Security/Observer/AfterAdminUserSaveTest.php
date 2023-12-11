<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Observer;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\Security\Observer\AfterAdminUserSave
 */
class AfterAdminUserSaveTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Save a new UserExpiration record
     *
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testSaveNewUserExpiration()
    {
        $adminUserNameFromFixture = 'dummy_username';
        $testDate = $this->getFutureDateInStoreTime();
        $user = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $user->loadByUsername($adminUserNameFromFixture);
        $user->setExpiresAt($testDate);
        $user->save();

        $userExpirationFactory =
            Bootstrap::getObjectManager()->create(\Magento\Security\Model\UserExpirationFactory::class);
        /** @var \Magento\Security\Model\UserExpiration $userExpiration */
        $userExpiration = $userExpirationFactory->create();
        $userExpiration->load($user->getId());
        static::assertNotNull($userExpiration->getId());
        static::assertEquals($userExpiration->getExpiresAt(), $testDate);
    }

    /**
     * Save a new UserExpiration; used to validate that date conversion is working correctly.
     *
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testSaveNewUserExpirationInMinutes()
    {
        $adminUserNameFromFixture = 'dummy_username';
        $testDate = $this->getFutureDateInStoreTime('+2 minutes');
        $user = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $user->loadByUsername($adminUserNameFromFixture);
        $user->setExpiresAt($testDate);
        $user->save();

        $userExpirationFactory =
            Bootstrap::getObjectManager()->create(\Magento\Security\Model\UserExpirationFactory::class);
        /** @var \Magento\Security\Model\UserExpiration $userExpiration */
        $userExpiration = $userExpirationFactory->create();
        $userExpiration->load($user->getId());
        static::assertNotNull($userExpiration->getId());
        static::assertEquals($userExpiration->getExpiresAt(), $testDate);
    }

    /**
     * Remove the UserExpiration record
     *
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testClearUserExpiration()
    {
        $adminUserNameFromFixture = 'adminUserExpired';
        $user = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $user->loadByUsername($adminUserNameFromFixture);
        $user->setExpiresAt(null);
        $user->save();

        $userExpirationFactory =
            Bootstrap::getObjectManager()->create(\Magento\Security\Model\UserExpirationFactory::class);
        /** @var \Magento\Security\Model\UserExpiration $userExpiration */
        $userExpiration = $userExpirationFactory->create();
        $userExpiration->load($user->getId());
        static::assertNull($userExpiration->getId());
    }

    /**
     * Change the UserExpiration record
     *
     * @magentoDataFixture Magento/Security/_files/expired_users.php
     */
    public function testChangeUserExpiration()
    {
        $adminUserNameFromFixture = 'adminUserNotExpired';
        $testDate = $this->getFutureDateInStoreTime();
        $user = Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
        $user->loadByUsername($adminUserNameFromFixture);

        $userExpirationFactory =
            Bootstrap::getObjectManager()->create(\Magento\Security\Model\UserExpirationFactory::class);
        /** @var \Magento\Security\Model\UserExpiration $userExpiration */
        $userExpiration = $userExpirationFactory->create();
        $userExpiration->load($user->getId());
        $existingExpiration = $userExpiration->getExpiresAt();

        $user->setExpiresAt($testDate);
        $user->save();
        $userExpiration->load($user->getId());
        static::assertNotNull($userExpiration->getId());
        static::assertEquals($userExpiration->getExpiresAt(), $testDate);
        static::assertNotEquals($existingExpiration, $userExpiration->getExpiresAt());
    }

    /**
     * @param string $timeToAdd Amount of time to add
     * @return string
     * @throws \Exception
     */
    private function getFutureDateInStoreTime($timeToAdd = '+20 days')
    {
        /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $locale */
        $locale = Bootstrap::getObjectManager()->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $testDate = new \DateTime();
        $testDate->modify($timeToAdd);
        $storeDate = $locale->date($testDate);
        return $storeDate->format('Y-m-d H:i:s');
    }
}
