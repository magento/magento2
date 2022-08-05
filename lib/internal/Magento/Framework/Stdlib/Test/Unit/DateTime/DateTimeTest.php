<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\Test\Unit\DateTime;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for @see DateTime
 */
class DateTimeTest extends TestCase
{
    /**
     * @var string
     */
    private $testDate = '2015-04-02 21:03:00';

    /**
     * @param int|string|DateTimeInterface $input
     * @throws Exception
     *
     * @dataProvider dateTimeInputDataProvider
     */
    public function testGmtTimestamp($input)
    {
        /** @var TimezoneInterface|MockObject $timezone */
        $timezone = $this->getMockBuilder(TimezoneInterface::class)
            ->getMock();
        $timezone->method('date')->willReturn(new \DateTime($this->testDate));

        $expected = gmdate('U', strtotime($this->testDate));
        $this->assertEquals($expected, (new DateTime($timezone))->gmtTimestamp($input));
    }

    /**
     * @param int|string|DateTimeInterface $input
     * @throws Exception
     *
     * @dataProvider dateTimeInputDataProvider
     */
    public function testTimestamp($input)
    {
        /** @var TimezoneInterface|MockObject $timezone */
        $timezone = $this->getMockBuilder(TimezoneInterface::class)
            ->getMock();
        $timezone->method('date')->willReturn(new \DateTime($this->testDate));

        $expected = gmdate('U', strtotime($this->testDate));
        $this->assertEquals($expected, (new DateTime($timezone))->timestamp($input));
    }

    public function testGtmOffset()
    {
        /** @var TimezoneInterface|MockObject $timezone */
        $timezone = $this->getMockBuilder(TimezoneInterface::class)
            ->getMock();
        $timezone->method('getConfigTimezone')->willReturn('Europe/Amsterdam');

        /** @var DateTime|MockObject $dateTime */
        $dateTime = $this->getMockBuilder(DateTime::class)
            ->setConstructorArgs([$timezone])
            ->setMethods(null)
            ->getMock();

        $this->assertEquals(
            $this->getExpectedGtmOffset($timezone->getConfigTimezone()),
            $dateTime->getGmtOffset()
        );
    }

    /**
     * Returns expected offset according to Daylight Saving Time in timezone
     *
     * @param string $timezoneIdentifier
     * @return int
     */
    private function getExpectedGtmOffset(string $timezoneIdentifier): int
    {
        $timeZoneToReturn = date_default_timezone_get();
        date_default_timezone_set($timezoneIdentifier);
        $expectedOffset = (date('I', time()) + 1) * 3600;
        date_default_timezone_set($timeZoneToReturn);

        return (int) $expectedOffset;
    }

    /**
     * Data provider
     *
     * @return array
     * @throws Exception
     */
    public function dateTimeInputDataProvider()
    {
        return [
            'string' => [$this->testDate],
            'int' => [strtotime($this->testDate)],
            DateTimeInterface::class => [new DateTimeImmutable($this->testDate)],
        ];
    }
}
