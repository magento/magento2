<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\Address\Billing;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WriteService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressFactoryMock;

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
    protected $converterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    protected function setUp()
    {
        $this->quoteRepositoryMock = $this->getMock('\Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->addressFactoryMock = $this->getMock(
            '\Magento\Sales\Model\Quote\AddressFactory', ['create', '__wakeup'], [], '', false
        );

        $this->quoteAddressMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Address',
            ['getCustomerId', 'load', 'getData', 'setData', 'setStreet', 'setRegionId', 'setRegion', '__wakeup'],
            [],
            '',
            false
        );
        $this->addressFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->quoteAddressMock));

        $this->validatorMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Address\Validator', [], [], '', false
        );

        $this->converterMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Address\Converter', [], [], '', false
        );

        $this->loggerMock = $this->getMock('\Psr\Log\LoggerInterface', [], [], '', false);

        $this->service = new \Magento\Checkout\Service\V1\Address\Billing\WriteService(
            $this->quoteRepositoryMock,
            $this->converterMock,
            $this->validatorMock,
            $this->addressFactoryMock,
            $this->loggerMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage error123
     */
    public function testSetAddressValidationFailed()
    {
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cartId')
            ->will($this->returnValue($quoteMock));

        $this->validatorMock->expects($this->once())->method('validate')
            ->will($this->throwException(new \Magento\Framework\Exception\NoSuchEntityException('error123')));

        $this->service->setAddress('cartId', null);
    }

    public function testSetAddress()
    {
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cartId')
            ->will($this->returnValue($quoteMock));

        $builder = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\Address\RegionBuilder', ['create'], [], '', false
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Checkout\Service\V1\Data\Cart\AddressBuilder $addressDataBuilder */
        $addressDataBuilder = $objectManager->getObject(
            'Magento\Checkout\Service\V1\Data\Cart\AddressBuilder',
            ['regionBuilder' => $builder]
        );
        /** @var \Magento\Checkout\Service\V1\Data\Cart\Address $addressData */
        $addressData = $addressDataBuilder->setId(454)->create();

        $this->validatorMock->expects($this->once())->method('validate')
            ->with($addressData)
        ->will($this->returnValue(true));

        $this->converterMock->expects($this->once())->method('convertDataObjectToModel')
            ->with($addressData, $this->quoteAddressMock)
            ->will($this->returnValue($this->quoteAddressMock));

        $quoteMock->expects($this->once())->method('setBillingAddress')->with($this->quoteAddressMock);
        $quoteMock->expects($this->once())->method('setDataChanges')->with(true);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $addressId = 1;
        $billingAddressMock = $this->getMock('\Magento\Sales\Model\Quote\Address', [], [], '', false);
        $billingAddressMock->expects($this->once())->method('getId')->will($this->returnValue($addressId));
        $quoteMock->expects($this->once())->method('getBillingAddress')
            ->will($this->returnValue($billingAddressMock));

        $this->assertEquals($addressId, $this->service->setAddress('cartId', $addressData));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Unable to save address. Please, check input data.
     */
    public function testSetAddressWithInabilityToSaveQuote()
    {
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cartId')
            ->will($this->returnValue($quoteMock));

        $builder = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\Address\RegionBuilder', ['create'], [], '', false
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        /** @var \Magento\Checkout\Service\V1\Data\Cart\AddressBuilder $addressDataBuilder */
        $addressDataBuilder = $objectManager->getObject(
            'Magento\Checkout\Service\V1\Data\Cart\AddressBuilder',
            ['regionBuilder' => $builder]
        );
        /** @var \Magento\Checkout\Service\V1\Data\Cart\Address $addressData */
        $addressData = $addressDataBuilder->setId(454)->create();

        $this->validatorMock->expects($this->once())->method('validate')
            ->with($addressData)
        ->will($this->returnValue(true));

        $this->converterMock->expects($this->once())->method('convertDataObjectToModel')
            ->with($addressData, $this->quoteAddressMock)
            ->will($this->returnValue($this->quoteAddressMock));

        $quoteMock->expects($this->once())->method('setBillingAddress')->with($this->quoteAddressMock);
        $quoteMock->expects($this->once())->method('setDataChanges')->with(true);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteMock)
            ->willThrowException(
                new \Exception('Some DB Error')
            );
        $this->service->setAddress('cartId', $addressData);
    }
}
