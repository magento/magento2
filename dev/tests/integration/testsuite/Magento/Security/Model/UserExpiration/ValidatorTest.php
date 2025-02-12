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
use PHPUnit\Framework\TestCase;

/**
 * Verify user expiration validation model.
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->validator = Bootstrap::getObjectManager()->get(Validator::class);
        $this->localeResolver = Bootstrap::getObjectManager()->get(ResolverInterface::class);
        $this->timeZone = Bootstrap::getObjectManager()->get(TimezoneInterface::class);
    }

    /**
     * Verify validation for date expiration with different locales.
     *
     * @magentoAppArea adminhtml
     * @dataProvider validateUserExpiresAtDataProvider
     * @return void
     */
    public function testValidateUserExpiresAt(string $locale): void
    {
        $this->markTestSkipped('Test is blocked by issue AC-285');
        $this->localeResolver->setLocale($locale);
        $date = $this->timeZone->date()->modify('+10 day');
        $expireDate = $this->timeZone->formatDateTime($date, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM);
        self::assertTrue($this->validator->isValid($expireDate));

        $date = $this->timeZone->date()->modify('-10 day');
        $expireDate = $this->timeZone->formatDateTime($date, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM);
        self::assertFalse($this->validator->isValid($expireDate));
        self::assertEquals(
            $this->validator->getMessages(),
            ['expires_at' => __('"%1" must be later than the current date.', 'Expiration date')]
        );
    }

    /**
     * Provides locale codes for validation test.
     *
     * @return array
     */
    public static function validateUserExpiresAtDataProvider(): array
    {
        return [
            'default' => [
                'locale' => 'en_US',
            ],
            'non_default_english_textual' => [
                'locale' => 'de_DE',
            ],
            'non_default_non_english_textual' => [
                'locale' => 'uk_UA',
            ],
        ];
    }
}
