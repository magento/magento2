<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Model\GuestShippingInformationManagement;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Validator\Factory as ValidatorFactory;
use Magento\Framework\Validator\ValidatorInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestShippingInformationManagementTest extends TestCase
{
    /**
     * @var ShippingInformationManagementInterface|MockObject
     */
    protected $shippingInformationManagementMock;

    /**
     * @var QuoteIdMaskFactory|MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var ValidatorFactory|MockObject
     */
    protected $validatorFactoryMock;

    /**
     * @var AddressFactory|MockObject
     */
    protected $addressFactoryMock;

    /**
     * @var GuestShippingInformationManagement
     */
    protected $model;

    protected function setUp(): void
    {
        $this->quoteIdMaskFactoryMock = $this->createPartialMock(
            QuoteIdMaskFactory::class,
            ['create']
        );
        $this->shippingInformationManagementMock = $this->createMock(
            ShippingInformationManagementInterface::class
        );
        $this->validatorFactoryMock = $this->createMock(ValidatorFactory::class);
        $this->addressFactoryMock = $this->createMock(AddressFactory::class);
        $this->model = new GuestShippingInformationManagement(
            $this->quoteIdMaskFactoryMock,
            $this->shippingInformationManagementMock,
            $this->validatorFactoryMock,
            $this->addressFactoryMock
        );
    }

    public function testSaveAddressInformation()
    {
        $cartId = 'masked_id';
        $quoteId = '100';
        $addressInformationMock = $this->getMockForAbstractClass(ShippingInformationInterface::class);
        $shippingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($shippingAddressMock);
        $shippingAddressMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);
        $customerAddressMock = $this->createMock(Address::class);
        $this->addressFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerAddressMock);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $this->validatorFactoryMock->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->willReturn(true);
        $quoteIdMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->addMethods(['getQuoteId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($quoteIdMaskMock);
        $quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($cartId, 'masked_id')
            ->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())->method('getQuoteId')->willReturn($quoteId);
        $paymentInformationMock = $this->getMockForAbstractClass(PaymentDetailsInterface::class);
        $this->shippingInformationManagementMock->expects($this->once())
            ->method('saveAddressInformation')
            ->with(
                self::callback(fn($actualQuoteId): bool => (int) $quoteId === $actualQuoteId),
                $addressInformationMock
            )
            ->willReturn($paymentInformationMock);
        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * Validate save address information when it is invalid
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSaveAddressInformationWithInvalidAddress()
    {
        $cartId = 'masked_id';
        $addressInformationMock = $this->getMockForAbstractClass(ShippingInformationInterface::class);
        $shippingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($shippingAddressMock);
        $shippingAddressMock->method('getExtensionAttributes')->willReturn(null);
        $customerAddressMock = $this->createMock(Address::class);
        $this->addressFactoryMock->expects($this->once())->method('create')->willReturn($customerAddressMock);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $this->validatorFactoryMock->expects($this->once())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->willReturn(false);
        $validatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn(['First Name is not valid!', 'Last Name is not valid!']);
        $this->expectException(InputException::class);
        $this->expectExceptionMessage(
            'The shipping address contains invalid data: First Name is not valid!, Last Name is not valid!'
        );
        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }
}
