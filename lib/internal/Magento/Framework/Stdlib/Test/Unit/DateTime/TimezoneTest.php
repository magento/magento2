<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\Test\Unit\DateTime;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\Intl\DateFormatterFactory;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see Timezone
 */
class TimezoneTest extends TestCase
{
    /**
     * @var string|null
     */
    private $defaultTimeZone;

    /**
     * @var string
     */
    private $scopeType;

    /**
     * @var string
     */
    private $defaultTimezonePath;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    private $scopeResolver;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolver;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $this->scopeType = 'store';
        $this->defaultTimezonePath = 'default/timezone/path';

        $this->objectManager = new ObjectManager($this);
        $this->scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
            ->getMock();
        $this->localeResolver = $this->getMockBuilder(ResolverInterface::class)
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        date_default_timezone_set($this->defaultTimeZone);
    }

    /**
     * Test date parsing with different includeTime options
     *
     * @param string $date
     * @param string $locale
     * @param bool $includeTime
     * @param int|string $expectedTime
     * @param string|null $timeZone
     * @dataProvider dateIncludeTimeDataProvider
     */
    public function testDateIncludeTime($date, $locale, $includeTime, $expectedTime, $timeZone = 'America/Chicago')
    {
        if ($timeZone !== null) {
            $this->scopeConfig->method('getValue')->willReturn($timeZone);
        }

        /** @var \DateTime $dateTime */
        $dateTime = $this->getTimezone()->date($date, $locale, $timeZone !== null, $includeTime);
        if (is_numeric($expectedTime)) {
            $this->assertEquals($expectedTime, $dateTime->getTimestamp());
        } else {
            $format = $includeTime ? DateTime::DATETIME_PHP_FORMAT : DateTime::DATE_PHP_FORMAT;
            $this->assertEquals($expectedTime, date($format, $dateTime->getTimestamp()));
        }
    }

    /**
     * DataProvider for testDateIncludeTime
     *
     * @return array
     */
    public static function dateIncludeTimeDataProvider(): array
    {
        /**
         * Greek locale needs to be installed on the system, to pass.
         *
         * 'Parse greek d/m/y date with time' => [
         * '30/10/2021, 12:01 π.μ.', // datetime
         * 'el_GR', // locale
         * true, // include time
         * 1635570060 // expected timestamp
         * ],
         */
        return [
            'Parse d/m/y date without time' => [
                '19/05/2017', // date
                'ar_KW', // locale
                false, // include time
                1495170000 // expected timestamp
            ],
            'Parse d/m/y date with time' => [
                '19/05/2017 00:01 صباحاً', // datetime (00:01 am)
                'ar_KW', // locale
                true, // include time
                1495170060 // expected timestamp
            ],
            'Parse m/d/y date without time' => [
                '05/19/2017', // date
                'en_US', // locale
                false, // include time
                1495170000 // expected timestamp
            ],
            'Parse m/d/y date with time' => [
                '05/19/2017 00:01 am', // datetime
                'en_US', // locale
                true, // include time
                1495170060 // expected timestamp
            ],
            'Parse greek d/m/y date without time' => [
                '30/10/2021', // datetime
                'el_GR', // locale
                false, // include time
                1635570000 // expected timestamp
            ],
            'Parse Saudi Arabia date without time' => [
                '4/09/2020',
                'ar_SA',
                false,
                '2020-09-04'
            ],
            'Parse Saudi Arabia date with time' => [
                '4/09/2020 10:10 مساء',
                'ar_SA',
                true,
                '2020-09-04 22:10:00',
                null
            ],
            'Parse Saudi Arabia date with zero time' => [
                '4/09/2020',
                'ar_SA',
                true,
                '2020-09-04 00:00:00',
                null
            ],
            'Parse date in short style with long year 1999' => [
                '8/11/1999',
                'en_US',
                false,
                '1999-08-11'
            ],
            'Parse date in short style with long year 2099' => [
                '9/2/2099',
                'en_US',
                false,
                '2099-09-02'
            ],
            'Parse date in short style with short year 1999' => [
                '8/11/99',
                'en_US',
                false,
                '1999-08-11'
            ],
        ];
    }

    /**
     * @param string $locale
     * @param int $style
     * @param string $expectedFormat
     * @dataProvider getDatetimeFormatDataProvider
     */
    public function testGetDatetimeFormat(string $locale, int $style, string $expectedFormat): void
    {
        /** @var Timezone $timezone */
        $this->localeResolver->method('getLocale')->willReturn($locale);
        $this->assertEquals($expectedFormat, $this->getTimezone()->getDateTimeFormat($style));
    }

    /**
     * @return array
     */
    public static function getDatetimeFormatDataProvider(): array
    {
        return [
            ['en_US', \IntlDateFormatter::SHORT, 'M/d/yy h:mm a'],
            ['ar_SA', \IntlDateFormatter::SHORT, 'd/MM/y h:mm a']
        ];
    }

    /**
     * @param string $locale
     * @param int $style
     * @param string $expectedFormat
     * @dataProvider getDateFormatWithLongYearDataProvider
     */
    public function testGetDateFormatWithLongYear(string $locale, string $expectedFormat): void
    {
        /** @var Timezone $timezone */
        $this->localeResolver->method('getLocale')->willReturn($locale);
        $this->assertEquals($expectedFormat, $this->getTimezone()->getDateFormatWithLongYear());
    }

    /**
     * @return array
     */
    public static function getDateFormatWithLongYearDataProvider(): array
    {
        return [
            ['en_US', 'M/d/y'],
        ];
    }

    /**
     * @param string $date
     * @param string $configuredTimezone
     * @param string $expectedResult
     * @dataProvider getConvertConfigTimeToUtcFixtures
     */
    public function testConvertConfigTimeToUtc($date, $configuredTimezone, $expectedResult)
    {
        $this->scopeConfigWillReturnConfiguredTimezone($configuredTimezone);

        $this->assertEquals($expectedResult, $this->getTimezone()->convertConfigTimeToUtc($date));
    }

    /**
     * Data provider for testConvertConfigTimeToUtc
     *
     * @return array
     */
    public static function getConvertConfigTimeToUtcFixtures(): array
    {
        return [
            'string' => [
                '2016-10-10 10:00:00',
                'UTC',
                '2016-10-10 10:00:00'
            ],
            'datetime' => [
                new \DateTime('2016-10-10 10:00:00', new \DateTimeZone('UTC')),
                'UTC',
                '2016-10-10 10:00:00'
            ],
            'datetimeimmutable' => [
                new \DateTimeImmutable('2016-10-10 10:00:00', new \DateTimeZone('UTC')),
                'UTC',
                '2016-10-10 10:00:00'
            ]
        ];
    }

    /**
     * Test configuration of the different timezones.
     * @dataProvider getDateFixtures
     */
    public function testDate($expectedResult, $timezone, $date)
    {
        $this->localeResolver->method('getLocale')->willReturn('en_GB');
        $this->scopeConfigWillReturnConfiguredTimezone($timezone);

        $this->assertEquals(
            $expectedResult()->format('DATE_ISO8601'),
            $this->getTimezone()->date($date)->format('DATE_ISO8601')
        );
    }

    /**
     * Data provider for testException
     *
     * @return array
     */
    public static function getConvertConfigTimeToUTCDataFixtures()
    {
        return [
            'datetime' => [
                new \DateTime('2016-10-10 10:00:00', new \DateTimeZone('UTC'))
            ]
        ];
    }

    /**
     * @dataProvider getConvertConfigTimeToUTCDataFixtures
     */
    public function testConvertConfigTimeToUtcException($date)
    {
        $this->expectException(LocalizedException::class);

        $this->getTimezone()->convertConfigTimeToUtc($date);
    }

    /**
     * DataProvider for testDate
     *
     * @return array
     */
    public static function getDateFixtures(): array
    {
        return [
            'now_datetime_utc' => [
                function () {
                    return new \DateTime('now', new \DateTimeZone('UTC'));
                },
                'UTC',
                null
            ],
            'fixed_datetime_utc' => [
                function () {
                    return new \DateTime('2017-01-01 10:00:00', new \DateTimeZone('UTC'));
                },
                'UTC',
                new \DateTime('2017-01-01 10:00:00', new \DateTimeZone('UTC'))
            ],
            'now_datetime_vancouver' => [
                function () {
                    return new \DateTime('now', new \DateTimeZone('America/Vancouver'));
                },
                'America/Vancouver',
                null
            ],
            'now_datetimeimmutable_utc' => [
                function () {
                    return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
                },
                'UTC',
                null
            ],
            'fixed_datetimeimmutable_utc' => [
                function () {
                    return new \DateTime('2017-01-01 10:00:00', new \DateTimeZone('UTC'));
                },
                'UTC',
                new \DateTimeImmutable('2017-01-01 10:00:00', new \DateTimeZone('UTC'))
            ],
            'now_datetimeimmutable_vancouver' => [
                function () {
                    return new \DateTimeImmutable('now', new \DateTimeZone('America/Vancouver'));
                },
                'America/Vancouver',
                null
            ],
        ];
    }

    /**
     * @return Timezone
     */
    private function getTimezone()
    {
        return new Timezone(
            $this->scopeResolver,
            $this->localeResolver,
            $this->createMock(DateTime::class),
            $this->scopeConfig,
            $this->scopeType,
            $this->defaultTimezonePath,
            new DateFormatterFactory()
        );
    }

    /**
     * @param string $configuredTimezone
     * @param string|null $scope
     */
    private function scopeConfigWillReturnConfiguredTimezone(string $configuredTimezone, ?string $scope = null)
    {
        $this->scopeConfig->expects($this->atLeastOnce())
            ->method('getValue')
            ->with($this->defaultTimezonePath, $this->scopeType, $scope)
            ->willReturn($configuredTimezone);
    }

    /**
     * @dataProvider scopeDateDataProvider
     * @param \DateTimeInterface|string|int $date
     * @param string $timezone
     * @param string $locale
     * @param string $expectedDate
     */
    public function testScopeDate($date, string $timezone, string $locale, string $expectedDate)
    {
        $scopeCode = 'test';

        $this->scopeConfigWillReturnConfiguredTimezone($timezone, $scopeCode);
        $this->localeResolver->method('getLocale')
            ->willReturn($locale);

        $scopeDate = $this->getTimezone()->scopeDate($scopeCode, $date, true);
        $this->assertEquals($expectedDate, $scopeDate->format('Y-m-d H:i:s'));
        $this->assertEquals($timezone, $scopeDate->getTimezone()->getName());
    }

    /**
     * @return array
     */
    public static function scopeDateDataProvider(): array
    {
        $utcTz = new \DateTimeZone('UTC');

        return [
            ['2018-10-20 00:00:00', 'UTC', 'en_US', '2018-10-20 00:00:00'],
            ['2018-10-20 00:00:00', 'America/Los_Angeles', 'en_US', '2018-10-19 17:00:00'],
            ['2018-10-20 00:00:00', 'Asia/Qatar', 'en_US', '2018-10-20 03:00:00'],
            ['2018-10-20 00:00:00', 'America/Los_Angeles', 'en_GB', '2018-10-19 17:00:00'],
            ['10/20/18 00:00', 'UTC', 'en_US', '2018-10-20 00:00:00'],
            ['10/20/18 00:00', 'America/Los_Angeles', 'en_US', '2018-10-19 17:00:00'],
            ['10/20/18 00:00', 'Asia/Qatar', 'en_US', '2018-10-20 03:00:00'],
            ['10/20/18 00:00', 'UTC', 'fr_FR', '2018-10-20 00:00:00'],
            ['10/20/18 00:00', 'America/Los_Angeles', 'fr_FR', '2018-10-19 17:00:00'],
            ['10/20/18 00:00', 'Asia/Qatar', 'fr_FR', '2018-10-20 03:00:00'],
            [1539993600, 'UTC', 'en_US', '2018-10-20 00:00:00'],
            [1539993600, 'America/Los_Angeles', 'en_US', '2018-10-19 17:00:00'],
            [1539993600, 'Asia/Qatar', 'en_US', '2018-10-20 03:00:00'],
            [new \DateTime('2018-10-20', $utcTz), 'UTC', 'en_US', '2018-10-20 00:00:00'],
            [new \DateTime('2018-10-20', $utcTz), 'America/Los_Angeles', 'en_US', '2018-10-19 17:00:00'],
            [new \DateTime('2018-10-20', $utcTz), 'Asia/Qatar', 'en_US', '2018-10-20 03:00:00'],
            [new \DateTimeImmutable('2018-10-20', $utcTz), 'UTC', 'en_US', '2018-10-20 00:00:00'],
            [new \DateTimeImmutable('2018-10-20', $utcTz), 'America/Los_Angeles', 'en_US', '2018-10-19 17:00:00'],
            [new \DateTimeImmutable('2018-10-20', $utcTz), 'Asia/Qatar', 'en_US', '2018-10-20 03:00:00'],
        ];
    }
}
