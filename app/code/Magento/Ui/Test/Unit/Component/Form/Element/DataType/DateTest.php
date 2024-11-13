<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form\Element\DataType;

use Exception;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\Intl\DateFormatterFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Form\Element\DataType\Date;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    /** @var Context|MockObject */
    private $contextMock;

    /** @var TimezoneInterface|MockObject */
    private $localeDateMock;

    /** @var ResolverInterface|MockObject */
    private $localeResolverMock;

    /** @var Date  */
    private $date;

    /** @var Processor|MockObject */
    private $processorMock;

    /** @var  ObjectManager */
    private $objectManagerHelper;

    /**
     * @var DateFormatterFactory|MockObject
     */
    private $dateFormatterFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->localeDateMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->objectManagerHelper = new ObjectManager($this);
        $this->processorMock = $this->createMock(Processor::class);
        $this->contextMock->method('getProcessor')->willReturn($this->processorMock);
        $this->dateFormatterFactoryMock = $this->getMockForAbstractClass(DateFormatterFactory::class);
    }

    /**
     * Test to Prepare component configuration with Time offset
     */
    public function testPrepareWithTimeOffset()
    {
        $this->date = new Date(
            $this->contextMock,
            $this->localeDateMock,
            $this->localeResolverMock,
            [],
            [
                'config' => [
                    'timeOffset' => 1,
                ],
            ]
        );

        $localeDateFormat = 'dd/MM/y';

        $this->localeDateMock->expects($this->once())
            ->method('getDateFormat')
            ->willReturn($localeDateFormat);

        $this->date->prepare();

        $config = $this->date->getConfig();
        $this->assertIsArray($config);

        $this->assertArrayHasKey('options', $config);
        $this->assertArrayHasKey('dateFormat', $config['options']);
        $this->assertEquals($localeDateFormat, $config['options']['dateFormat']);
    }

    /**
     * Test to Prepare component configuration without Time offset
     */
    public function testPrepareWithoutTimeOffset()
    {
        $defaultDateFormat = 'MM/dd/y';

        $this->date = new Date(
            $this->contextMock,
            $this->localeDateMock,
            $this->localeResolverMock,
            [],
            [
                'config' => [
                    'options' => [
                        'dateFormat' => $defaultDateFormat,
                    ],
                    'outputDateFormat' => $defaultDateFormat,
                ],
            ]
        );

        $localeDateFormat = 'dd/MM/y';

        $this->localeDateMock->expects($this->once())
            ->method('getDateFormat')
            ->willReturn($localeDateFormat);
        $this->localeDateMock->expects($this->any())
            ->method('getConfigTimezone')
            ->willReturn('America/Los_Angeles');

        $this->date->prepare();

        $config = $this->date->getConfig();
        $this->assertIsArray($config);

        $this->assertArrayHasKey('options', $config);
        $this->assertArrayHasKey('dateFormat', $config['options']);
        $this->assertEquals($localeDateFormat, $config['options']['dateFormat']);
    }

    /**
     * This tests ensures that userTimeZone is properly saved in the configuration
     */
    public function testPrepare()
    {
        $this->localeResolverMock->expects($this->any())->method('getLocale')->willReturn('de-DE');
        $this->date = $this->objectManagerHelper->getObject(
            Date::class,
            [
                'context' => $this->contextMock,
                'localeDate' => $this->localeDateMock,
                'localeResolver' => $this->localeResolverMock
            ]
        );
        $this->localeDateMock->expects($this->any())->method('getConfigTimezone')->willReturn('America/Chicago');
        $this->date->prepare();
        $configArray = $this->date->getData('config');
        $this->assertEquals('America/Chicago', $configArray['storeTimeZone']);
        $this->assertEquals('de-DE', $configArray['options']['storeLocale']);
    }

    /**
     * Test to Convert given date to default (UTC) timezone
     *
     * @param string $dateStr
     * @param bool $setUtcTimeZone
     * @param string $convertedDate
     * @dataProvider convertDatetimeDataProvider
     */
    public function testConvertDatetime(string $dateStr, bool $setUtcTimeZone, string $convertedDate)
    {
        $this->localeDateMock->method('getConfigTimezone')
            ->willReturn('America/Los_Angeles');

        $this->date = $this->objectManagerHelper->getObject(
            Date::class,
            [
                'localeDate' => $this->localeDateMock,
            ]
        );

        $this->assertEquals(
            $convertedDate,
            $this->date->convertDatetime($dateStr, $setUtcTimeZone)->format('Y-m-d H:i:s'),
            "The date value wasn't converted"
        );
    }

    /**
     * @return array
     */
    public static function convertDatetimeDataProvider(): array
    {
        return [
            ['2019-09-30T12:32:00.000Z', false, '2019-09-30 12:32:00'],
            ['2019-09-30T12:32:00.000', false, '2019-09-30 12:32:00'],
            ['2019-09-30T12:32:00.000Z', true, '2019-09-30 19:32:00'],
        ];
    }

    /**
     * Run test for convertDateFormat() method
     *
     * @param string $date
     * @param string $locale
     * @param string $expected
     * @return void
     * @dataProvider convertDateFormatDataProvider
     * @throws Exception
     */
    public function testConvertDateFormat(
        string $date,
        string $locale,
        string $expected
    ): void {
        $this->localeResolverMock
            ->expects($this->any())
            ->method('getLocale')
            ->willReturn($locale);
        $this->date = $this->objectManagerHelper->getObject(
            Date::class,
            [
                'localeResolver' => $this->localeResolverMock,
                'dateFormatterFactory' => $this->dateFormatterFactoryMock
            ]
        );
        $this->assertEquals(
            $expected,
            $this->date->convertDateFormat($date)
        );
    }

    /**
     * DataProvider for testConvertDateFormat()
     *
     * @return array
     */
    public static function convertDateFormatDataProvider(): array
    {
        return [
            [
                '2023-10-15',
                'en_US',
                '10/15/2023'
            ],
            [
                '10/15/2023',
                'en_US',
                '10/15/2023'
            ],
            [
                '2023-10-15',
                'en_GB',
                '15/10/2023'
            ],
            [
                '15/10/2023',
                'en_GB',
                '15/10/2023'
            ],
            [
                '2023-10-15',
                'ja_JP',
                '2023/10/15'
            ],
            [
                '2023/10/15',
                'ja_JP',
                '2023/10/15'
            ]
        ];
    }
}
