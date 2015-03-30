<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use \Magento\Quote\Model\BillingAddressManagement;

class BillingAddressManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BillingAddressManagement
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
     * @return void
     */
    protected function setUp()
    {
        $this->quoteRepositoryMock = $this->getMock('\Magento\Quote\Model\QuoteRepository', [], [], '', false);
        $this->validatorMock = $this->getMock('\Magento\Quote\Model\QuoteAddressValidator', [], [], '', false);
        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $this->model = new BillingAddressManagement($this->quoteRepositoryMock, $this->validatorMock, $logger);
    }

    /**
     * @return void
     */
    public function testGetAddress()
    {
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')
            ->with('cartId')->will($this->returnValue($quoteMock));

        $addressMock = $this->getMock('\Magento\Quote\Model\Quote\Address', [], [], '', false);
        $quoteMock->expects($this->any())->method('getBillingAddress')->will($this->returnValue($addressMock));

        $this->assertEquals($addressMock, $this->model->get('cartId'));
    }


    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage error123
     */
    public function testSetAddressValidationFailed()
    {
        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cartId')
            ->will($this->returnValue($quoteMock));

        $this->validatorMock->expects($this->once())->method('validate')
            ->will($this->throwException(new \Magento\Framework\Exception\NoSuchEntityException(__('error123'))));

        $this->model->assign('cartId', $address);
    }

    /**
     * @return void
     */
    public function testSetAddress()
    {
        $address = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false, false);

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cartId')
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

        $this->assertEquals($addressId, $this->model->assign('cartId', $address));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Unable to save address. Please, check input data.
     */
    public function testSetAddressWithInabilityToSaveQuote()
    {
        $address = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false, false);

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cartId')
            ->will($this->returnValue($quoteMock));

        $this->validatorMock->expects($this->once())->method('validate')
            ->with($address)
            ->will($this->returnValue(true));

        $quoteMock->expects($this->once())->method('setBillingAddress')->with($address);
        $quoteMock->expects($this->once())->method('setDataChanges')->with(true);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteMock)
            ->willThrowException(
                new \Exception('Some DB Error')
            );
        $this->model->assign('cartId', $address);
    }
}
