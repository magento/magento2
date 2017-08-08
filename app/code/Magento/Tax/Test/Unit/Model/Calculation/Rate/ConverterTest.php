<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\Calculation\Rate;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Model\Calculation\Rate\Converter
     */
    protected $converter;

    /**
     * @var \Magento\Tax\Api\Data\TaxRateInterfaceFactory
     */
    protected $taxRateDataObjectFactory;

    /**
     * @var \Magento\Tax\Api\Data\TaxRateTitleInterfaceFactory
     */
    protected $taxRateTitleDataObjectFactory;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->taxRateDataObjectFactory = $this->getMockBuilder(
            \Magento\Tax\Api\Data\TaxRateInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->taxRateTitleDataObjectFactory = $this->getMockBuilder(
            \Magento\Tax\Api\Data\TaxRateTitleInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->converter =  $this->objectManager->getObject(
            \Magento\Tax\Model\Calculation\Rate\Converter::class,
            [
                'taxRateDataObjectFactory' =>  $this->taxRateDataObjectFactory,
                'taxRateTitleDataObjectFactory' => $this->taxRateTitleDataObjectFactory,
            ]
        );
    }

    public function testCreateTitlesFromServiceObject()
    {
        $taxRateMock = $this->createMock(\Magento\Tax\Api\Data\TaxRateInterface::class);
        $titlesMock = $this->createMock(\Magento\Tax\Api\Data\TaxRateTitleInterface::class);

        $taxRateMock->expects($this->once())->method('getTitles')->willReturn([$titlesMock]);
        $titlesMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $titlesMock->expects($this->once())->method('getValue')->willReturn('Value');

        $this->assertEquals([1 => 'Value'], $this->converter->createTitleArrayFromServiceObject($taxRateMock));
    }

    public function testCreateTitlesFromServiceObjectWhenTitlesAreNotProvided()
    {
        $taxRateMock = $this->createMock(\Magento\Tax\Api\Data\TaxRateInterface::class);

        $taxRateMock->expects($this->once())->method('getTitles')->willReturn([]);

        $this->assertEquals([], $this->converter->createTitleArrayFromServiceObject($taxRateMock));
    }

    public function testCreateArrayFromServiceObject()
    {
        $taxRateMock = $this->createMock(\Magento\Tax\Api\Data\TaxRateInterface::class);
        $titlesMock = $this->createMock(\Magento\Tax\Api\Data\TaxRateTitleInterface::class);

        $taxRateMock->expects($this->atLeastOnce())->method('getTitles')->willReturn([$titlesMock]);
        $titlesMock->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $titlesMock->expects($this->atLeastOnce())->method('getValue')->willReturn('Value');

        $this->assertArrayHasKey('title[1]', $this->converter->createArrayFromServiceObject($taxRateMock, true));
        $this->assertArrayHasKey('title', $this->converter->createArrayFromServiceObject($taxRateMock));
        $this->assertTrue(is_array($this->converter->createArrayFromServiceObject($taxRateMock)));
    }

    public function testPopulateTaxRateData()
    {
        $rateTitles = [$this->objectManager->getObject(
            \Magento\Tax\Model\Calculation\Rate\Title::class,
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
            \Magento\Tax\Model\Calculation\Rate::class,
            [
                'data' =>$dataArray,
            ]
        );

        $this->taxRateDataObjectFactory->expects($this->once())->method('create')->willReturn($taxRate);

        $this->assertSame($taxRate, $this->converter->populateTaxRateData($dataArray));
        $this->assertEquals($taxRate->getTitles(), $rateTitles);
    }
}
