<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\GuestCart;

class GuestShippingAddressManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Api\GuestShippingAddressManagementInterface
     */
    protected $model;

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
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->quoteRepositoryMock = $this->getMock('\Magento\Quote\Model\QuoteRepository', [], [], '', false);
        $this->quoteAddressMock = $this->getMock( '\Magento\Quote\Model\Quote\Address', [], [], '', false);
        $this->validatorMock = $this->getMock( 'Magento\Quote\Model\QuoteAddressValidator', [], [], '', false);
        $this->quoteIdMaskFactoryMock = $this->getMock('Magento\Quote\Model\QuoteIdMaskFactory', [], [], '', false);
        $this->quoteIdMaskMock = $this->getMock('Magento\Quote\Model\QuoteIdMask', [], [], '', false);
        $this->model = $objectManager->getObject(
            'Magento\Quote\Model\GuestCart\GuestShippingAddressManagement',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'addressValidator' => $this->validatorMock,
                'logger' => $this->getMock('\Psr\Log\LoggerInterface'),
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock
            ]
        );
    }

    public function testAssignAddress()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 867;

        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('getId')
            ->willReturn($cartId);

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));


        $this->validatorMock->expects($this->once())->method('validate')
            ->with($this->quoteAddressMock)
            ->will($this->returnValue(true));

        $quoteMock->expects($this->once())->method('setShippingAddress')->with($this->quoteAddressMock);
        $quoteMock->expects($this->once())->method('setDataChanges')->with(true);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $addressId = 1;
        $shippingAddressMock = $this->getMock('\Magento\Quote\Model\Quote\Address', [], [], '', false);
        $shippingAddressMock->expects($this->once())->method('getId')->will($this->returnValue($addressId));
        $quoteMock->expects($this->once())->method('getShippingAddress')
            ->will($this->returnValue($shippingAddressMock));

        $this->assertEquals($addressId, $this->model->assign($maskedCartId, $this->quoteAddressMock));
    }

    public function testGetAddress()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 867;

        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('getId')
            ->willReturn($cartId);

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)->will(
            $this->returnValue($quoteMock)
        );

        $addressMock = $this->getMock('\Magento\Quote\Model\Quote\Address', [], [], '', false);
        $quoteMock->expects($this->any())->method('getShippingAddress')->will($this->returnValue($addressMock));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(false));
        $this->assertEquals($addressMock, $this->model->get($maskedCartId));
    }
}
