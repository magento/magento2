<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Checks\CanUseForCountry;

class CountryProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    public function setUp()
    {
        $this->directoryMock = $this->getMock('Magento\Directory\Helper\Data', [], [], '', false, false);
        $this->model = new \Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider($this->directoryMock);
    }

    public function testGetCountryForNonVirtualQuote()
    {
        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false, false);
        $quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $addressMock = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false, false);
        $addressMock->expects($this->once())->method('getCountry')->will($this->returnValue(1));
        $quoteMock->expects($this->once())->method('getShippingAddress')->will($this->returnValue($addressMock));
        $this->assertEquals(1, $this->model->getCountry($quoteMock));
    }

    public function testGetCountryForVirtualQuote()
    {
        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false, false);
        $quoteMock->expects($this->once())->method('isVirtual')->willReturn(true);
        $addressMock = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false, false);
        $addressMock->expects($this->never())->method('getCountry');
        $quoteMock->expects($this->never())->method('getShippingAddress');
        $this->directoryMock->expects($this->once())->method('getDefaultCountry')->willReturn(10);
        $this->assertEquals(10, $this->model->getCountry($quoteMock));
    }
}
