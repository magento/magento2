<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit\DateTime;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Magento\Framework\Stdlib\DateTimeTest test case
 */
class DateTimeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $testDate = '2015-04-02 21:03:00';

    /**
     * @param int|string|\DateTimeInterface $input
     * @dataProvider dateTimeInputDataProvider
     */
    public function testGmtTimestamp($input)
    {
        /** @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject $timezone */
        $timezone = $this->getMockBuilder(TimezoneInterface::class)->getMock();
        $timezone->method('date')->willReturn(new \DateTime($this->testDate));

        $expected = gmdate('U', strtotime($this->testDate));
        $this->assertEquals($expected, (new DateTime($timezone))->gmtTimestamp($input));
    }

    /**
     * @param int|string|\DateTimeInterface $input
     * @dataProvider dateTimeInputDataProvider
     */
    public function testTimestamp($input)
    {
        /** @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject $timezone */
        $timezone = $this->getMockBuilder(TimezoneInterface::class)->getMock();
        $timezone->method('date')->willReturn(new \DateTime($this->testDate));

        $expected = gmdate('U', strtotime($this->testDate));
        $this->assertEquals($expected, (new DateTime($timezone))->timestamp($input));
    }

    /**
     * @return array
     */
    public function dateTimeInputDataProvider()
    {
        return [
            'string' => [$this->testDate],
            'int' => [strtotime($this->testDate)],
            \DateTimeInterface::class => [new \DateTimeImmutable($this->testDate)],
        ];
    }
}
