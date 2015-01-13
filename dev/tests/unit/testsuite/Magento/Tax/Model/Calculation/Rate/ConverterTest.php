<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Calculation\Rate;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\Calculation\Rate\Converter
     */
    protected $converter;

    public function setUp()
    {
        $this->converter = new Converter();
    }

    public function testCreateTitlesFromServiceObject()
    {
        $taxRateMock = $this->getMock('Magento\Tax\Api\Data\TaxRateInterface');
        $titlesMock = $this->getMock('Magento\Tax\Api\Data\TaxRateTitleInterface');

        $taxRateMock->expects($this->once())->method('getTitles')->willReturn([$titlesMock]);
        $titlesMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $titlesMock->expects($this->once())->method('getValue')->willReturn('Value');

        $this->assertEquals([1 => 'Value'], $this->converter->createTitleArrayFromServiceObject($taxRateMock));
    }

    public function testCreateTitlesFromServiceObjectWhenTitlesAreNotProvided()
    {
        $taxRateMock = $this->getMock('Magento\Tax\Api\Data\TaxRateInterface');

        $taxRateMock->expects($this->once())->method('getTitles')->willReturn([]);

        $this->assertEquals([], $this->converter->createTitleArrayFromServiceObject($taxRateMock));
    }
}
