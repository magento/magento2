<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit\DateTime\Filter;

use \Magento\Framework\Stdlib\DateTime\Filter\DateTime;

class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $inputData
     * @param string $expectedDate
     *
     * @dataProvider dateTimeFilterDataProvider
     */
    public function testFilter($inputData, $expectedDate)
    {
        $localeMock = $this->getMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $localeMock->expects(
            $this->once()
        )->method(
            'getDateTimeFormat'
        )->with(
            \IntlDateFormatter::SHORT
        )->will(
            $this->returnValue('HH:mm:ss MM-dd-yyyy')
        );
        $model = new DateTime($localeMock);

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
        $this->setExpectedException('\Exception');

        $localeMock = $this->getMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $localeMock->expects(
            $this->once()
        )->method(
            'getDateFormat'
        )->with(
            \IntlDateFormatter::SHORT
        )->will(
            $this->returnValue('MM-dd-yyyy')
        );
        $model = new DateTime($localeMock);

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
