<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Form\Element\DataType;

use Exception;
use Magento\Customer\Ui\Component\Form\Element\DataType\Date;
use Magento\Framework\Stdlib\DateTime\Intl\DateFormatterFactory;
use Magento\Ui\Component\Form\Element\DataType\Date as UiComponentDate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class \Magento\Customer\Ui\Component\Form\Element\DataType\Date
 */
class DateTest extends TestCase
{
    /**
     * @var Date
     */
    protected Date $model;

    /**
     * @var DateFormatterFactory|MockObject
     */
    private $dateFormatterFactoryMock;

    /**
     * @var UiComponentDate|MockObject
     */
    private $subjectMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->dateFormatterFactoryMock = $this->getMockForAbstractClass(DateFormatterFactory::class);
        $this->subjectMock = $this->getMockBuilder(UiComponentDate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Date(
            $this->dateFormatterFactoryMock
        );
    }

    /**
     * Run test for beforeConvertDate() method
     *
     * @param string $date
     * @param string $locale
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param bool $setUtcTimeZone
     * @param array $expected
     * @return void
     * @dataProvider beforeConvertDateDataProvider
     * @throws Exception
     */
    public function testBeforeConvertDate(
        string $date,
        string $locale,
        int $hour,
        int $minute,
        int $second,
        bool $setUtcTimeZone,
        array $expected
    ): void {
        $this->subjectMock->expects($this->once())
            ->method('getLocale')
            ->willReturn($locale);
        $this->assertEquals(
            $expected,
            $this->model->beforeConvertDate(
                $this->subjectMock,
                $date,
                $hour,
                $minute,
                $second,
                $setUtcTimeZone
            )
        );
    }

    /**
     * DataProvider for testBeforeConvertDate()
     *
     * @return array
     */
    public function beforeConvertDateDataProvider(): array
    {
        return [
            [
                '2023-10-15',
                'en_US',
                0,
                0,
                0,
                true,
                ['10/15/2023', 0, 0, 0, true]
            ],
            [
                '10/15/2023',
                'en_US',
                0,
                0,
                0,
                true,
                ['10/15/2023', 0, 0, 0, true]
            ],
            [
                '2023-10-15',
                'en_GB',
                0,
                0,
                0,
                true,
                ['15/10/2023', 0, 0, 0, true]
            ],
            [
                '15/10/2023',
                'en_GB',
                0,
                0,
                0,
                true,
                ['15/10/2023', 0, 0, 0, true]
            ],
            [
                '2023-10-15',
                'ja_JP',
                0,
                0,
                0,
                true,
                ['2023/10/15', 0, 0, 0, true]
            ],
            [
                '2023/10/15',
                'ja_JP',
                0,
                0,
                0,
                true,
                ['2023/10/15', 0, 0, 0, true]
            ]
        ];
    }
}
