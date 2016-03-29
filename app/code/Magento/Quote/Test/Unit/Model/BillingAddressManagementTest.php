<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use \Magento\Quote\Model\BillingAddressManagement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BillingAddressManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepository;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock('\Magento\Quote\Api\CartRepositoryInterface');
        $this->validatorMock = $this->getMock('\Magento\Quote\Model\QuoteAddressValidator', [], [], '', false);
        $this->addressRepository = $this->getMock('\Magento\Customer\Api\AddressRepositoryInterface');
        $logger = $this->getMock('\Psr\Log\LoggerInterface');
        $this->model = $this->objectManager->getObject(
            '\Magento\Quote\Model\BillingAddressManagement',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'addressValidator' => $this->validatorMock,
                'logger' => $logger,
                'addressRepository' => $this->addressRepository
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
        $this->markTestSkipped('MAGETWO-48531');
        $address = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cartId')
            ->will($this->returnValue($quoteMock));

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->will($this->throwException(new \Magento\Framework\Exception\NoSuchEntityException(__('error123'))));

        $this->model->assign('cartId', $address);
    }

    /**
     * @return void
     */
    public function testSetAddress()
    {
        $this->markTestSkipped('MAGETWO-48531');
        $cartId = 100;
        $useForShipping = true;
        $addressId = 1;
        $customerAddressId = 10;

        $address = $this->getMock(
            'Magento\Quote\Model\Quote\Address',
            ['setSaveInAddressBook', 'getCustomerAddressId', 'getSaveInAddressBook'],
            [],
            '',
            false
        );
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);
        $this->validatorMock->expects($this->once())->method('validate')
            ->with($address)
            ->willReturn(true);

        $address->expects($this->once())->method('getCustomerAddressId')->willReturn($customerAddressId);
        $address->expects($this->once())->method('getSaveInAddressBook')->willReturn(1);

        $customerAddressMock = $this->getMock('Magento\Customer\Api\Data\AddressInterface', [], [], '', false);
        $this->addressRepository->expects($this->once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);

        $quoteBillingAddress = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $quoteBillingAddress->expects($this->once())->method('getId')->will($this->returnValue($addressId));
        $quoteMock->expects($this->exactly(2))->method('getBillingAddress')->willReturn($quoteBillingAddress);
        $quoteBillingAddress->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();

        $quoteShippingAddress = $this->getMock(
            'Magento\Quote\Model\Quote\Address',
            ['setSaveInAddressBook', 'setSameAsBilling', 'setCollectShippingRates', 'importCustomerAddressData'],
            [],
            '',
            false
        );
        $quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($quoteShippingAddress);
        $quoteShippingAddress->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();
        $quoteShippingAddress->expects($this->once())->method('setSaveInAddressBook')->with(1)->willReturnSelf();

        $quoteBillingAddress->expects($this->once())->method('setSaveInAddressBook')->with(1)->willReturnSelf();
        $quoteMock->expects($this->once())->method('setBillingAddress')->with($quoteBillingAddress)->willReturnSelf();

        $quoteShippingAddress->expects($this->once())->method('setSameAsBilling')->with(1)->willReturnSelf();
        $quoteShippingAddress->expects($this->once())->method('setCollectShippingRates')->with(true)->willReturnSelf();

        $quoteMock->expects($this->once())->method('setShippingAddress')->with($quoteShippingAddress);
        $quoteMock->expects($this->once())->method('setDataChanges')->with(true);

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $this->assertEquals($addressId, $this->model->assign($cartId, $address, $useForShipping));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Unable to save address. Please check input data.
     */
    public function testSetAddressWithInabilityToSaveQuote()
    {
        $this->markTestSkipped('MAGETWO-48531');
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
