<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit\DateTime\Filter;

use \Magento\Framework\Stdlib\DateTime\Filter\Date;

class DateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $inputData
     * @param string $expectedDate
     *
     * @dataProvider dateFilterDataProvider
     */
    public function testFilter($inputData, $expectedDate)
    {
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
        $model = new Date($localeMock);
        $localeMock->expects($this->once())->method('date')->willReturn(new \DateTime($inputData));

        $this->assertEquals($expectedDate, $model->filter($inputData));
    }

    /**
     * @return array
     */
    public function dateFilterDataProvider()
    {
        return [
            ['2000-01-01', '2000-01-01'],
            ['2014-03-30T02:30:00', '2014-03-30'],
            ['12/31/2000', '2000-12-31']
        ];
    }

    /**
     * @dataProvider dateFilterWithExceptionDataProvider
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
        $model = new Date($localeMock);
        $localeMock->expects($this->any())->method('date')->willReturn(new \DateTime($inputData));

        $model->filter($inputData);
    }

    /**
     * @return array
     */
    public function dateFilterWithExceptionDataProvider()
    {
        return [
            ['12-31-2000'],
            ['22/2000-01'],
        ];
    }
}
