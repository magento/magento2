<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\Address\Shipping;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    protected function setUp()
    {
        $this->quoteRepositoryMock = $this->getMock('\Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->converterMock = $this->getMock('\Magento\Checkout\Service\V1\Address\Converter', [], [], '', false);

        $this->service = new ReadService($this->quoteRepositoryMock, $this->converterMock);
    }

    public function testGetAddress()
    {
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with('cartId')->will(
            $this->returnValue($quoteMock)
        );

        $addressMock = $this->getMock('\Magento\Sales\Model\Quote\Address', [], [], '', false);
        $quoteMock->expects($this->any())->method('getShippingAddress')->will($this->returnValue($addressMock));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(false));

        $this->converterMock->expects($this->once())->method('convertModelToDataObject')
            ->with($addressMock)->will($this->returnValue('ShippingAddress'));

        $this->assertEquals('ShippingAddress', $this->service->getAddress('cartId'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cart contains virtual product(s) only. Shipping address is not applicable
     */
    public function testGetAddressOfQuoteWithVirtualProducts()
    {
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with('cartId')->will(
            $this->returnValue($quoteMock)
        );

        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(true));
        $quoteMock->expects($this->never())->method('getShippingAddress');

        $this->service->getAddress('cartId');
    }
}
