<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit\DateTime\Filter;

use \Magento\Framework\Stdlib\DateTime\Filter\DateTime;

class DateTimeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $inputData
     * @param string $expectedDate
     *
     * @dataProvider dateTimeFilterDataProvider
     */
    public function testFilter($inputData, $expectedDate)
    {
        $localeMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $localeMock->expects(
            $this->once()
        )->method(
            'getDateTimeFormat'
        )->with(
            \IntlDateFormatter::SHORT
        )->willReturn(
            'HH:mm:ss MM-dd-yyyy'
        );

        $model = new DateTime($localeMock);
        $localeMock->expects($this->once())->method('date')->willReturn(new \DateTime($inputData));

        $this->assertEquals($expectedDate, $model->filter($inputData));
    }

    /**
     * @return array
     */
    public function dateTimeFilterDataProvider()
    {
        return [
            ['2000-01-01 02:30:00', '2000-01-01 02:30:00'],
            ['2014-03-30T02:30:00', '2014-03-30 02:30:00'],
            ['12/31/2000 02:30:00', '2000-12-31 02:30:00'],
            ['02:30:00 12/31/2000', '2000-12-31 02:30:00'],
        ];
    }

    /**
     * @dataProvider dateTimeFilterWithExceptionDataProvider
     */
    public function testFilterWithException($inputData)
    {
        $this->expectException('\Exception');

        $localeMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $localeMock->expects(
            $this->once()
        )->method(
            'getDateFormat'
        )->with(
            \IntlDateFormatter::SHORT
        )->willReturn(
            'MM-dd-yyyy'
        );
        $model = new DateTime($localeMock);

        $localeMock->expects($this->any())->method('date')->willReturn(new \DateTime($inputData));
        $model->filter($inputData);
    }

    /**
     * @return array
     */
    public function dateTimeFilterWithExceptionDataProvider()
    {
        return [
            ['12-31-2000 22:22:22'],
            ['22/2000-01 22:22:22'],
        ];
    }
}
