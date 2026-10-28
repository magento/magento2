<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\Test\Unit\DateTime\Filter;

use DateTime;
use Exception;
use IntlDateFormatter;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DateTest extends TestCase
{
    /**
     * @param string $inputData
     * @param string $expectedDate
     *     */
    #[DataProvider('dateFilterDataProvider')]
    public function testFilter($inputData, $expectedDate)
    {
        $localeMock = $this->createMock(TimezoneInterface::class);
        $localeMock->expects(
            $this->once()
        )->method(
            'getDateFormat'
        )->with(
            IntlDateFormatter::SHORT
        )->willReturn(
            'MM-dd-yyyy'
        );
        $model = new Date($localeMock);
        $localeMock->expects($this->once())->method('date')->willReturn(new DateTime($inputData));

        $this->assertEquals($expectedDate, $model->filter($inputData));
    }

    /**
     * @return array
     */
    public static function dateFilterDataProvider()
    {
        return [
            ['2000-01-01', '2000-01-01'],
            ['2014-03-30T02:30:00', '2014-03-30'],
            ['12/31/2000', '2000-12-31']
        ];
    }

    /**     */
    #[DataProvider('dateFilterWithExceptionDataProvider')]
    public function testFilterWithException($inputData)
    {
        $this->expectException(Exception::class);

        $localeMock = $this->createMock(TimezoneInterface::class);
        $localeMock->expects(
            $this->once()
        )->method(
            'getDateFormat'
        )->with(
            IntlDateFormatter::SHORT
        )->willReturn(
            'MM-dd-yyyy'
        );
        $model = new Date($localeMock);
        $localeMock->expects($this->any())->method('date')->willReturn(new DateTime($inputData));

        $model->filter($inputData);
    }

    /**
     * @return array
     */
    public static function dateFilterWithExceptionDataProvider()
    {
        return [
            ['12-31-2000'],
            ['22/2000-01'],
        ];
    }
}
