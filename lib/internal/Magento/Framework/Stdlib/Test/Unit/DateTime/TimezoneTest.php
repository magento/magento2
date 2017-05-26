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

class TimezoneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string|null
     */
    private static $defaultTimeZone;

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

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)->getMock();
        $this->localeResolver = $this->getMockBuilder(ResolverInterface::class)->getMock();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
    }

    public static function tearDownAfterClass()
    {
        date_default_timezone_set(static::$defaultTimeZone);
    }

    /**
     * Test date parsing with different includeTime options
     *
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

    public function dateIncludeTimeDataProvider()
    {
        return [
            'Parse date without time' => [
                '19/05/2017', // date
                'ar_KW', // locale
                false, // include time
                1495170000 // expected timestamp
            ],
            'Parse date with time' => [
                '05/19/2017 00:01 am', // date
                'en_US', // locale
                true, // include time
                1495170060 // expected timestamp
            ],
        ];
    }

    /**
     * @dataProvider getConvertConfigTimeToUtcFixtures
     */
    public function testConvertConfigTimeToUtc($date, $configuredTimezone, $expectedResult)
    {
        $this->scopeConfigWillReturnConfiguredTimezone($configuredTimezone);

        $this->assertEquals($expectedResult, $this->getTimezone()->convertConfigTimeToUtc($date));
    }

    public function getConvertConfigTimeToUtcFixtures()
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
     * @dataProvider getDateFixtures
     */
    public function testDate(callable $expectedResult, $timezone = 'UTC', $date = null)
    {
        $this->localeResolver
            ->method('getLocale')
            ->willReturn('en_GB')
        ;

        $this->scopeConfigWillReturnConfiguredTimezone($timezone);

        $this->assertEquals(
            $expectedResult(),
            $this->getTimezone()->date($date, null, true),
            '',
            1
        );
    }

    public function getDateFixtures()
    {
        static::$defaultTimeZone = date_default_timezone_get();

        date_default_timezone_set('UTC');

        return [
            'now_datetime_utc' => [
                function () {
                    return new \DateTime('now', new \DateTimeZone('UTC'));
                },
                'UTC'
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
                'America/Vancouver'
            ],
            'now_datetimeimmutable_utc' => [
                function () {
                    return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
                },
                'UTC'
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
                'America/Vancouver'
            ],
        ];
    }

    private function getTimezone()
    {
        return new Timezone(
            $this->scopeResolver,
            $this->localeResolver,
            $this->getMockBuilder(DateTime::class)->getMock(),
            $this->scopeConfig,
            '',
            ''
        );
    }

    private function scopeConfigWillReturnConfiguredTimezone($configuredTimezone)
    {
        $this->scopeConfig
            ->method('getValue')
            ->with('', '', null)
            ->willReturn($configuredTimezone);
    }
}
