<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\UserExpiration;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\UserFactory;
use PHPUnit\Framework\TestCase;

/**
 * Verify user expiration validation model.
 */
class ValidatorTest extends TestCase
{
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->localeResolver = Bootstrap::getObjectManager()->get(ResolverInterface::class);
        $this->timeZone = Bootstrap::getObjectManager()->get(TimezoneInterface::class);
    }

    /**
     * Verify validation is passed on non US locale for date expiration.
     *
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testValidateUserExpiresAtWithNonUsLocale(): void
    {
        $user = Bootstrap::getObjectManager()->get(UserFactory::class)->create();
        $user->setUserName('testUser');
        $user->setEmail('john.doe@example.com');
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setPassword('testPassword123');

        $this->localeResolver->setLocale('uk_UA');
        $date = $this->timeZone->date()->modify('+10 day');
        $expireDate = $this->timeZone->formatDateTime($date);
        $user->setExpiresAt($expireDate);
        self::assertTrue($user->validate());

        $date = $this->timeZone->date()->modify('-10 day');
        $expireDate = $this->timeZone->formatDateTime($date);
        $user->setExpiresAt($expireDate);
        self::assertEquals(
            $user->validate(),
            [__('"%1" must be later than the current date.', 'Expiration date')]
        );
    }
}
