<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\ResourceModel;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Security\Model\UserExpirationFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\UserFactory;
use PHPUnit\Framework\TestCase;

/**
 * Verify user expiration resource model.
 */
class UserExpirationTest extends TestCase
{
    /**
     * @var UserExpiration
     */
    private $userExpirationResource;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->userExpirationResource = Bootstrap::getObjectManager()->get(UserExpiration::class);
    }

    /**
     * Verify user expiration saved with correct date.
     *
     * @magentoAppArea adminhtml
     */
    public function testUserExpirationSave(): void
    {
        $localeResolver = Bootstrap::getObjectManager()->get(ResolverInterface::class);
        $timeZone = Bootstrap::getObjectManager()->get(TimezoneInterface::class);
        $localeResolver->setLocale('uk_UA');
        $date = $timeZone->date()->modify('+10 day');
        $expireDate = $timeZone->formatDateTime($date);
        $userId = $this->getUser();
        $userExpirationFactory = Bootstrap::getObjectManager()->get(UserExpirationFactory::class);
        $userExpiration = $userExpirationFactory->create();
        $userExpiration->setExpiresAt($expireDate);
        $userExpiration->setUserId($userId);
        $this->userExpirationResource->save($userExpiration);
        $loadedUserExpiration = $userExpirationFactory->create();
        $this->userExpirationResource->load($loadedUserExpiration, $userExpiration->getId());

        self::assertEquals($expireDate, $timeZone->formatDateTime($userExpiration->getExpiresAt()));
    }

    /**
     * Create and save user.
     *
     * @return int
     */
    private function getUser(): int
    {
        $userFactory = Bootstrap::getObjectManager()->get(UserFactory::class);
        $userResource = Bootstrap::getObjectManager()->get(UserResource::class);
        $user = $userFactory->create();
        $user->setUserName('testUser');
        $user->setEmail('john.doe@example.com');
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setPassword('testPassword123');
        $userResource->save($user);

        return (int)$user->getId();
    }
}
