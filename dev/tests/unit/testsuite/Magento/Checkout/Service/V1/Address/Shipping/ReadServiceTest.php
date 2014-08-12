<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $quoteLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->quoteLoaderMock = $this->getMock('\Magento\Checkout\Service\V1\QuoteLoader', [], [], '', false);
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->converterMock = $this->getMock('\Magento\Checkout\Service\V1\Address\Converter', [], [], '', false);

        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue(123));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));

        $this->service = new ReadService($this->quoteLoaderMock, $this->converterMock, $this->storeManagerMock);
    }

    public function testGetAddress()
    {
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $this->quoteLoaderMock->expects($this->once())->method('load')->with('cartId', '123')->will(
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
        $this->quoteLoaderMock->expects($this->once())->method('load')->with('cartId', '123')->will(
            $this->returnValue($quoteMock)
        );

        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(true));
        $quoteMock->expects($this->never())->method('getShippingAddress');

        $this->service->getAddress('cartId');
    }
}
