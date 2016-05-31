<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Quote\Test\Unit\Model;

use \Magento\Quote\Model\ShippingAddressManagement;
class ShippingAddressManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShippingAddressManagement
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsCollectorMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock('\Magento\Quote\Api\CartRepositoryInterface');
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->quoteAddressMock = $this->getMock(
            '\Magento\Quote\Model\Quote\Address',
            [
                'setSameAsBilling',
                'setCollectShippingRates',
                '__wakeup',
                'collectTotals',
                'save',
                'getId',
                'getCustomerAddressId',
                'getSaveInAddressBook'
            ],
            [],
            '',
            false
        );
        $this->validatorMock = $this->getMock(
            'Magento\Quote\Model\QuoteAddressValidator', [], [], '', false
        );
        $this->totalsCollectorMock = $this->getMock('Magento\Quote\Model\Quote\TotalsCollector', [], [], '', false);
        $this->service = $this->objectManager->getObject(
            '\Magento\Quote\Model\ShippingAddressManagement',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'addressValidator' => $this->validatorMock,
                'logger' => $this->getMock('\Psr\Log\LoggerInterface'),
                'scopeConfig' => $this->scopeConfigMock,
                'totalsCollector' => $this->totalsCollectorMock
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expected ExceptionMessage error345
     */
    public function testSetAddressValidationFailed()
    {
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cart654')
            ->will($this->returnValue($quoteMock));

        $this->validatorMock->expects($this->once())->method('validate')
            ->will($this->throwException(new \Magento\Framework\Exception\NoSuchEntityException(__('error345'))));

        $this->service->assign('cart654', $this->quoteAddressMock);
    }

    public function testSetAddress()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $addressId = 1;
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cart867')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));


        $this->validatorMock->expects($this->once())->method('validate')
            ->with($this->quoteAddressMock)
            ->will($this->returnValue(true));
        $this->totalsCollectorMock
            ->expects($this->once())
            ->method('collectAddressTotals')
            ->with($quoteMock, $this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())->method('save')->willReturnSelf();
        $this->quoteAddressMock->expects($this->once())->method('getId')->will($this->returnValue($addressId));

        $quoteMock->expects($this->any())
            ->method('setShippingAddress')
            ->with($this->quoteAddressMock)
            ->willReturnSelf();
        $quoteMock->expects($this->any())
            ->method('getShippingAddress')
            ->will($this->returnValue($this->quoteAddressMock));
        $quoteMock->expects($this->once())->method('validateMinimumAmount')->willReturn(true);

        $this->assertEquals($addressId, $this->service->assign('cart867', $this->quoteAddressMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart contains virtual product(s) only. Shipping address is not applicable
     */
    public function testSetAddressForVirtualProduct()
    {
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cart867')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(true));
        $quoteMock->expects($this->never())->method('setShippingAddress');

        $this->quoteAddressMock->expects($this->never())->method('getCustomerAddressId');
        $this->quoteAddressMock->expects($this->never())->method('setSaveInAddressBook');

        $quoteMock->expects($this->never())->method('save');

        $this->service->assign('cart867', $this->quoteAddressMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Unable to save address. Please check input data.
     */
    public function testSetAddressWithInabilityToSaveQuote()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $this->quoteAddressMock->expects($this->once())->method('save')->willThrowException(
            new \Exception('Unable to save address. Please check input data.')
        );

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->totalsCollectorMock
            ->expects($this->once())
            ->method('collectAddressTotals')
            ->with($quoteMock, $this->quoteAddressMock);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cart867')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($this->quoteAddressMock);

        $this->validatorMock->expects($this->once())->method('validate')
            ->with($this->quoteAddressMock)
            ->will($this->returnValue(true));
        $this->service->assign('cart867', $this->quoteAddressMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testSetAddressWithViolationOfMinimumAmount()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $storeId = 12;
        $this->quoteAddressMock->expects($this->once())->method('save');

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with('cart123')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($this->quoteAddressMock);
        $quoteMock->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $this->totalsCollectorMock
            ->expects($this->once())
            ->method('collectAddressTotals')
            ->with($quoteMock, $this->quoteAddressMock);
        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->with('sales/minimum_order/error_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

        $this->service->assign('cart123', $this->quoteAddressMock);
    }

    public function testGetAddress()
    {
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with('cartId')->will(
            $this->returnValue($quoteMock)
        );

        $addressMock = $this->getMock('\Magento\Quote\Model\Quote\Address', [], [], '', false);
        $quoteMock->expects($this->any())->method('getShippingAddress')->will($this->returnValue($addressMock));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(false));
        $this->assertEquals($addressMock, $this->service->get('cartId'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cart contains virtual product(s) only. Shipping address is not applicable
     */
    public function testGetAddressOfQuoteWithVirtualProducts()
    {
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with('cartId')->will(
            $this->returnValue($quoteMock)
        );

        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(true));
        $quoteMock->expects($this->never())->method('getShippingAddress');

        $this->service->get('cartId');
    }
}
