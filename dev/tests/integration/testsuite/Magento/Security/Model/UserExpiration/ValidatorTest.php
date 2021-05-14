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
 * Verify user expiration validator.
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
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->validator = Bootstrap::getObjectManager()->get(Validator::class);
        $this->localeResolver = Bootstrap::getObjectManager()->get(ResolverInterface::class);
        $this->timeZone = Bootstrap::getObjectManager()->get(TimezoneInterface::class);
    }

    /**
     * Verify valid expiration date with non-default locale.
     *
     * @return void
     */
    public function testValidExpirationDateWithNonDefaultLocale(): void
    {
        $this->localeResolver->setLocale('de_DE');
        $expireDate = $this->timeZone->date()->modify('+1 day');
        self::assertTrue($this->validator->isValid(
            $this->timeZone->formatDateTime($expireDate, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM))
        );
    }

    /**
     * Verify invalid expiration date with non-default locale.
     *
     * @return void
     */
    public function testInvalidExpirationDateWithNonDefaultLocale(): void
    {
        $this->localeResolver->setLocale('de_DE');
        $expireDate = $this->timeZone->date()->modify('-1 day');
        self::assertFalse($this->validator->isValid(
            $this->timeZone->formatDateTime($expireDate, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM))
        );
    }
}
