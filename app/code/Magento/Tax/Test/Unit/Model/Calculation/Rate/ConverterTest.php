<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Calculation\Rate;

use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Api\Data\TaxRateInterfaceFactory;
use Magento\Tax\Api\Data\TaxRateTitleInterface;
use Magento\Tax\Api\Data\TaxRateTitleInterfaceFactory;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Calculation\Rate\Converter;
use Magento\Tax\Model\Calculation\Rate\Title;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @var TaxRateInterfaceFactory|MockObject
     */
    protected $taxRateDataObjectFactory;

    /**
     * @var TaxRateTitleInterfaceFactory|MockObject
     */
    protected $taxRateTitleDataObjectFactory;

    /**
     * @var FormatInterface|MockObject
     */
    private $format;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->taxRateDataObjectFactory = $this->getMockBuilder(
            TaxRateInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->taxRateTitleDataObjectFactory = $this->getMockBuilder(
            TaxRateTitleInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->format = $this->getMockBuilder(FormatInterface::class)
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->converter =  $this->objectManager->getObject(
            Converter::class,
            [
                'taxRateDataObjectFactory' =>  $this->taxRateDataObjectFactory,
                'taxRateTitleDataObjectFactory' => $this->taxRateTitleDataObjectFactory,
                'format' => $this->format,
            ]
        );
    }

    public function testCreateTitlesFromServiceObject()
    {
        $taxRateMock = $this->getMockForAbstractClass(TaxRateInterface::class);
        $titlesMock = $this->getMockForAbstractClass(TaxRateTitleInterface::class);

        $taxRateMock->expects($this->once())->method('getTitles')->willReturn([$titlesMock]);
        $titlesMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $titlesMock->expects($this->once())->method('getValue')->willReturn('Value');

        $this->assertEquals([1 => 'Value'], $this->converter->createTitleArrayFromServiceObject($taxRateMock));
    }

    public function testCreateTitlesFromServiceObjectWhenTitlesAreNotProvided()
    {
        $taxRateMock = $this->getMockForAbstractClass(TaxRateInterface::class);

        $taxRateMock->expects($this->once())->method('getTitles')->willReturn([]);

        $this->assertEquals([], $this->converter->createTitleArrayFromServiceObject($taxRateMock));
    }

    public function testCreateArrayFromServiceObject()
    {
        $taxRateMock = $this->getMockForAbstractClass(TaxRateInterface::class);
        $titlesMock = $this->getMockForAbstractClass(TaxRateTitleInterface::class);

        $taxRateMock->expects($this->atLeastOnce())->method('getTitles')->willReturn([$titlesMock]);
        $titlesMock->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $titlesMock->expects($this->atLeastOnce())->method('getValue')->willReturn('Value');

        $this->assertArrayHasKey('title[1]', $this->converter->createArrayFromServiceObject($taxRateMock, true));
        $this->assertArrayHasKey('title', $this->converter->createArrayFromServiceObject($taxRateMock));
        $this->assertIsArray($this->converter->createArrayFromServiceObject($taxRateMock));
    }

    public function testPopulateTaxRateData()
    {
        $rateTitles = [$this->objectManager->getObject(
            Title::class,
            ['data' => ['store_id' => 1, 'value' => 'texas']]
        )
        ];
        $dataArray=[
            'tax_country_id' => 'US',
            'tax_region_id' => 2,
            'tax_postcode' => null,
            'rate' => 7.5,
            'code' => 'Tax Rate Code',
            'titles' => $rateTitles,
        ];

        $taxRate = $this->objectManager->getObject(
            Rate::class,
            [
                'data' => $dataArray,
            ]
        );

        $this->taxRateDataObjectFactory->expects($this->once())->method('create')->willReturn($taxRate);

        $this->format->expects($this->once())->method('getNumber')->willReturnArgument(0);

        $this->assertSame($taxRate, $this->converter->populateTaxRateData($dataArray));
        $this->assertEquals($taxRate->getTitles(), $rateTitles);
    }
}
