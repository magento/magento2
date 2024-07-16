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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->userExpirationResource = Bootstrap::getObjectManager()->get(UserExpiration::class);
    }

    /**
     * Verify user expiration saved with correct date.
     *
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     * @dataProvider userExpirationSaveDataProvider
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testUserExpirationSave(string $locale): void
    {
        $localeResolver = Bootstrap::getObjectManager()->get(ResolverInterface::class);
        $timeZone = Bootstrap::getObjectManager()->get(TimezoneInterface::class);
        $localeResolver->setLocale($locale);
        $initialExpirationDate = $timeZone->date()->modify('+10 day');
        $expireDate = $timeZone->formatDateTime(
            $initialExpirationDate,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
        if (version_compare(PHP_VERSION, '8.3', '>=') && $locale === 'uk_UA') {
            $expireDate = str_replace(' р.', ' р.', $expireDate);
        }
        $userExpirationFactory = Bootstrap::getObjectManager()->get(UserExpirationFactory::class);
        $userExpiration = $userExpirationFactory->create();
        $userExpiration->setExpiresAt($expireDate);
        $userExpiration->setUserId($this->getUserId());
        $this->userExpirationResource->save($userExpiration);
        $loadedUserExpiration = $userExpirationFactory->create();
        $this->userExpirationResource->load($loadedUserExpiration, $userExpiration->getId());

        self::assertEquals($initialExpirationDate->format('Y-m-d H:i:s'), $loadedUserExpiration->getExpiresAt());
    }

    /**
     * Provides locale codes for validation test.
     *
     * @return array
     */
    public function userExpirationSaveDataProvider(): array
    {
        return [
            'default' => [
                'locale_code' => 'en_US',
            ],
            'non_default_english_textual' => [
                'locale_code' => 'de_DE',
            ],
            'non_default_non_english_textual' => [
                'locale_code' => 'uk_UA',
            ],
        ];
    }

    /**
     * Retrieve user id from db.
     *
     * @return int
     */
    private function getUserId(): int
    {
        $userResource = Bootstrap::getObjectManager()->get(UserResource::class);
        $data = $userResource->loadByUsername('dummy_username');

        return (int)$data['user_id'];
    }
}
