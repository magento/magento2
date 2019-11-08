<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit\DateTime;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for @see Timezone
 */
class TimezoneTest extends \PHPUnit\Framework\TestCase
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
     * @var ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeResolver;

    /**
     * @var ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolver;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $this->scopeType = 'store';
        $this->defaultTimezonePath = 'default/timezone/path';

        $this->objectManager = new ObjectManager($this);
        $this->scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)->getMock();
        $this->localeResolver = $this->getMockBuilder(ResolverInterface::class)->getMock();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        date_default_timezone_set($this->defaultTimeZone);
    }

    /**
     * Test date parsing with different includeTime options
     *
     * @param string $date
     * @param string $locale
     * @param bool $includeTime
     * @param int $expectedTimestamp
     * @dataProvider dateIncludeTimeDataProvider
     */
    public function testDateIncludeTime($date, $locale, $includeTime, $expectedTimestamp)
    {
        $this->scopeConfig->method('getValue')->willReturn('America/Chicago');
        /** @var Timezone $timezone */
        $timezone = $this->objectManager->getObject(Timezone::class, ['scopeConfig' => $this->scopeConfig]);

        /** @var \DateTime $dateTime */
        $dateTime = $timezone->date($date, $locale, true, $includeTime);
        $this->assertEquals($expectedTimestamp, $dateTime->getTimestamp());
    }

    /**
     * DataProvider for testDateIncludeTime
     *
     * @return array
     */
    public function dateIncludeTimeDataProvider(): array
    {
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
    public function getConvertConfigTimeToUtcFixtures(): array
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
     */
    public function testDate()
    {
        $dateFixtures = $this->getDateFixtures();
        foreach ($dateFixtures as $dateFixture) {
            $expectedResult = $dateFixture[0];
            $timezone = $dateFixture[1];
            $date = $dateFixture[2];

            $this->localeResolver->method('getLocale')->willReturn('en_GB');
            $this->scopeConfigWillReturnConfiguredTimezone($timezone);

            $this->assertEquals(
                $expectedResult(),
                $this->getTimezone()->date($date, null, true),
                '',
                1
            );
        }
    }

    /**
     * DataProvider for testDate
     *
     * @return array
     */
    private function getDateFixtures(): array
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
                new \DateTime('2017-01-01 10:00:00')
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
                new \DateTimeImmutable('2017-01-01 10:00:00')
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
            $this->defaultTimezonePath
        );
    }

    /**
     * @param string $configuredTimezone
     * @param string|null $scope
     */
    private function scopeConfigWillReturnConfiguredTimezone(string $configuredTimezone, string $scope = null)
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
    public function scopeDateDataProvider(): array
    {
        $utcTz = new \DateTimeZone('UTC');

        return [
            ['2018-10-20 00:00:00', 'UTC', 'en_US', '2018-10-20 00:00:00'],
            ['2018-10-20 00:00:00', 'America/Los_Angeles', 'en_US', '2018-10-20 00:00:00'],
            ['2018-10-20 00:00:00', 'Asia/Qatar', 'en_US', '2018-10-20 00:00:00'],
            ['10/20/18 00:00', 'UTC', 'en_US', '2018-10-20 00:00:00'],
            ['10/20/18 00:00', 'America/Los_Angeles', 'en_US', '2018-10-20 00:00:00'],
            ['10/20/18 00:00', 'Asia/Qatar', 'en_US', '2018-10-20 00:00:00'],
            ['20/10/18 00:00', 'UTC', 'fr_FR', '2018-10-20 00:00:00'],
            ['20/10/18 00:00', 'America/Los_Angeles', 'fr_FR', '2018-10-20 00:00:00'],
            ['20/10/18 00:00', 'Asia/Qatar', 'fr_FR', '2018-10-20 00:00:00'],
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
