<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Quote\Test\Unit\Model\GuestCart\GuestCartTestHelper;

class GuestBillingAddressManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\GuestCart\GuestBillingAddressManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

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

    /**
     * @var string
     */
    protected $maskedCartId;

    /**
     * @var int
     */
    protected $cartId;

    /**
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock('\Magento\Quote\Model\QuoteRepository', [], [], '', false);
        $this->validatorMock = $this->getMock('\Magento\Quote\Model\QuoteAddressValidator', [], [], '', false);
        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $this->quoteIdMaskFactoryMock = $this->getMock('Magento\Quote\Model\QuoteIdMaskFactory', [], [], '', false);
        $this->quoteIdMaskMock = $this->getMock('Magento\Quote\Model\QuoteIdMask', [], [], '', false);

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 11;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $objectManager->getObject(
            'Magento\Quote\Model\GuestCart\GuestBillingAddressManagement',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'addressValidator' => $this->validatorMock,
                'logger' => $logger,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetAddress()
    {
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')
            ->with($this->cartId )->will($this->returnValue($quoteMock));

        $addressMock = $this->getMock('\Magento\Quote\Model\Quote\Address', [], [], '', false);
        $quoteMock->expects($this->any())->method('getBillingAddress')->will($this->returnValue($addressMock));

        $this->assertEquals($addressMock, $this->model->get($this->maskedCartId ));
    }

    /**
     * @return void
     */
    public function testAssingAddress()
    {
        $address = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false, false);

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($this->cartId )
            ->will($this->returnValue($quoteMock));

        $this->validatorMock->expects($this->once())->method('validate')
            ->with($address)
            ->will($this->returnValue(true));

        $quoteMock->expects($this->once())->method('setBillingAddress')->with($address);
        $quoteMock->expects($this->once())->method('setDataChanges')->with(true);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $addressId = 1;
        $billingAddressMock = $this->getMock('\Magento\Quote\Model\Quote\Address', [], [], '', false);
        $billingAddressMock->expects($this->once())->method('getId')->will($this->returnValue($addressId));
        $quoteMock->expects($this->once())->method('getBillingAddress')
            ->will($this->returnValue($billingAddressMock));

        $this->assertEquals($addressId, $this->model->assign($this->maskedCartId , $address));
    }
}
