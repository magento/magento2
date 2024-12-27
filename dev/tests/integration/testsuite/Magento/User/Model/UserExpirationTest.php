<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Model;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Security\Model\ResourceModel\UserExpiration;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Test\Fixture\User as UserDataFixture;
use Magento\Security\Model\UserExpirationFactory;
use PHPUnit\Framework\TestCase;

class UserExpirationTest extends TestCase
{

    /**
     * @var UserExpiration
     */
    private $userExpirationResource;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * @var UserExpiration
     */
    private $userExpiration;

    /**
     * @var UserExpirationFactory
     */
    private $userExpirationFactory;

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->userExpirationResource = Bootstrap::getObjectManager()->get(UserExpiration::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->userExpirationFactory = Bootstrap::getObjectManager()->get(UserExpirationFactory::class);
        $this->timeZone = Bootstrap::getObjectManager()->get(TimezoneInterface::class);
    }

    /**
     * Verify user expiration saved with large date.
     *
     * @throws LocalizedException
     * @return void
     */
    #[
        DataFixture(UserDataFixture::class, ['role_id' => 1], 'user')
    ]
    public function testLargeExpirationDate(): void
    {
        $user = $this->fixtures->get('user');
        $userId = $user->getDataByKey('user_id');

        // Get date more than 100 years from current date
        $initialExpirationDate = $this->timeZone->date()->modify('+100 years');
        $expireDate = $this->timeZone->formatDateTime(
            $initialExpirationDate,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );

        // Set Expiration date to the admin user and save
        $this->setExpirationDateToUser($expireDate, (int)$userId);

        // Load admin expiration date from database
        $loadedUserExpiration = $this->userExpirationFactory->create();
        $this->userExpirationResource->load($loadedUserExpiration, $this->userExpiration->getId());

        self::assertEquals(
            strtotime($initialExpirationDate->format('Y-m-d H:i:s')),
            strtotime($loadedUserExpiration->getExpiresAt())
        );
    }

    /**
     * Set expiration date to admin user and save
     *
     * @param string $expirationDate
     * @param int $userId
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function setExpirationDateToUser(string $expirationDate, int $userId): void
    {
        $this->userExpiration = $this->userExpirationFactory->create();
        $this->userExpiration->setExpiresAt($expirationDate);
        $this->userExpiration->setUserId($userId);
        $this->userExpirationResource->save($this->userExpiration);
    }
}
