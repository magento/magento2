<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\Test\Unit\DateTime\Filter;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\Filter\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DateTimeTest extends TestCase
{
    /**
     * @var DateTime
     */
    protected $model;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDateMock;

    /**
     * @var ResolverInterface|MockObject
     */
    protected $localeResolverMock;

    protected function setUp(): void
    {
        $this->localeDateMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->localeResolverMock->expects($this->any())->method('getLocale')->willReturn('en_US');

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            DateTime::class,
            [
                'localeDate' => $this->localeDateMock,
                'localeResolver' => $this->localeResolverMock
            ]
        );
    }

    /**
     * @param string $inputData
     * @param string $expectedDate
     * @dataProvider dateTimeFilterDataProvider
     */
    public function testFilter($inputData, $expectedDate)
    {
        $this->localeDateMock->expects($this->once())
            ->method('formatDateTime')
            ->with(
                $inputData,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::SHORT,
                'en_US'
            )->willReturn($inputData);

        $this->localeDateMock->expects($this->once())->method('date')->willReturn(new \DateTime($inputData));

        $this->assertEquals($expectedDate, $this->model->filter($inputData));
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
        $this->localeDateMock->expects($this->any())->method('date')->willReturn(new \DateTime($inputData));
        $this->model->filter($inputData);
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
