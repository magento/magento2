<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ShippingInformationManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDetailsFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartTotalsRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Checkout\Model\ShippingInformationManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartExtensionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartExtensionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $addressValidatorMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentMethodManagementMock = $this->createMock(
            \Magento\Quote\Api\PaymentMethodManagementInterface::class
        );
        $this->paymentDetailsFactoryMock = $this->createPartialMock(
            \Magento\Checkout\Model\PaymentDetailsFactory::class,
            ['create']
        );
        $this->cartTotalsRepositoryMock = $this->createMock(\Magento\Quote\Api\CartTotalRepositoryInterface::class);
        $this->quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->shippingAddressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            [
                'getSaveInAddressBook',
                'getSameAsBilling',
                'getCustomerAddressId',
                'setShippingAddress',
                'getShippingAddress',
                'setSaveInAddressBook',
                'setSameAsBilling',
                'setCollectShippingRates',
                'getCountryId',
                'importCustomerAddressData',
                'save',
                'getShippingRateByCode',
                'getShippingMethod',
                'setLimitCarrier'
            ]
        );

        $this->quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            [
                'isVirtual',
                'getItemsCount',
                'getIsMultiShipping',
                'setIsMultiShipping',
                'validateMinimumAmount',
                'getStoreId',
                'setShippingAddress',
                'getShippingAddress',
                'collectTotals',
                'getExtensionAttributes',
                'setExtensionAttributes',
                'setBillingAddress'
            ],
            [],
            '',
            false
        );

        $this->shippingAssignmentFactoryMock =
            $this->createPartialMock(\Magento\Quote\Model\ShippingAssignmentFactory::class, ['create']);
        $this->cartExtensionFactoryMock =
            $this->createPartialMock(\Magento\Quote\Api\Data\CartExtensionFactory::class, ['create']);
        $this->shippingFactoryMock =
            $this->createPartialMock(\Magento\Quote\Model\ShippingFactory::class, ['create']);
        $this->addressValidatorMock = $this->createMock(
            \Magento\Quote\Model\QuoteAddressValidator::class
        );

        $this->model = $this->objectManager->getObject(
            \Magento\Checkout\Model\ShippingInformationManagement::class,
            [
                'paymentMethodManagement' => $this->paymentMethodManagementMock,
                'paymentDetailsFactory' => $this->paymentDetailsFactoryMock,
                'cartTotalsRepository' => $this->cartTotalsRepositoryMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'shippingAssignmentFactory' => $this->shippingAssignmentFactoryMock,
                'cartExtensionFactory' => $this->cartExtensionFactoryMock,
                'shippingFactory' => $this->shippingFactoryMock,
                'addressValidator' => $this->addressValidatorMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The shipping method can't be set for an empty cart. Add an item to cart and try again.
     */
    public function testSaveAddressInformationIfCartIsEmpty()
    {
        $cartId = 100;
        $addressInformationMock = $this->createMock(\Magento\Checkout\Api\Data\ShippingInformationInterface::class);

        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(0);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * @param string $shippingMethod
     */
    private function setShippingAssignmentsMocks($shippingMethod)
    {
        $this->quoteMock->expects($this->once())->method('getExtensionAttributes')->willReturn(null);
        $this->shippingAddressMock->expects($this->once())->method('setLimitCarrier');
        $this->cartExtensionMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\CartExtension::class,
            ['getShippingAssignments', 'setShippingAssignments']
        );
        $this->cartExtensionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cartExtensionMock);
        $this->cartExtensionMock->expects($this->once())->method('getShippingAssignments')->willReturn(null);

        $this->shippingAssignmentMock = $this->createMock(
            \Magento\Quote\Model\ShippingAssignment::class
        );
        $this->shippingAssignmentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->shippingAssignmentMock);
        $this->shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn(null);

        $this->shippingMock = $this->createMock(\Magento\Quote\Model\Shipping::class);
        $this->shippingFactoryMock->expects($this->once())->method('create')->willReturn($this->shippingMock);

        $this->shippingMock->expects($this->once())
            ->method('setAddress')
            ->with($this->shippingAddressMock)
            ->willReturnSelf();
        $this->shippingMock->expects($this->once())->method('setMethod')->with($shippingMethod)->willReturnSelf();

        $this->shippingAssignmentMock->expects($this->once())
            ->method('setShipping')
            ->with($this->shippingMock)
            ->willReturnSelf();

        $this->cartExtensionMock->expects($this->once())
            ->method('setShippingAssignments')
            ->with([$this->shippingAssignmentMock])
            ->willReturnSelf();

        $this->quoteMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->cartExtensionMock)
            ->willReturnSelf();
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage The shipping address is missing. Set the address and try again.
     */
    public function testSaveAddressInformationIfShippingAddressNotSet()
    {
        $cartId = 100;
        $addressInformationMock = $this->createMock(\Magento\Checkout\Api\Data\ShippingInformationInterface::class);
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(null);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(100);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The shipping information was unable to be saved. Verify the input data and try again.
     */
    public function testSaveAddressInformationIfCanNotSaveQuote()
    {
        $cartId = 100;
        $carrierCode = 'carrier_code';
        $shippingMethod = 'shipping_method';
        $addressInformationMock = $this->createMock(\Magento\Checkout\Api\Data\ShippingInformationInterface::class);

        $this->addressValidatorMock->expects($this->exactly(2))
            ->method('validateForCart');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())->method('getShippingMethodCode')->willReturn($shippingMethod);

        $billingAddress = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $addressInformationMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddress);

        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn('USA');

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(100);
        $this->quoteMock->expects($this->once())->method('setIsMultiShipping')->with(false)->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('setBillingAddress')->with($billingAddress)->willReturnSelf();

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException(new \Exception());

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Carrier with such method not found: carrier_code, shipping_method
     */
    public function testSaveAddressInformationIfCarrierCodeIsInvalid()
    {
        $cartId = 100;
        $carrierCode = 'carrier_code';
        $shippingMethod = 'shipping_method';
        $addressInformationMock = $this->createMock(\Magento\Checkout\Api\Data\ShippingInformationInterface::class);

        $this->addressValidatorMock->expects($this->exactly(2))
            ->method('validateForCart');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())->method('getShippingMethodCode')->willReturn($shippingMethod);

        $billingAddress = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $addressInformationMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddress);
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn('USA');

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(100);
        $this->quoteMock->expects($this->once())->method('setIsMultiShipping')->with(false)->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('setBillingAddress')->with($billingAddress)->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($this->shippingAddressMock);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock);

        $this->shippingAddressMock->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with($shippingMethod)
            ->willReturn(false);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    public function testSaveAddressInformation()
    {
        $cartId = 100;
        $carrierCode = 'carrier_code';
        $shippingMethod = 'shipping_method';
        $addressInformationMock = $this->createMock(\Magento\Checkout\Api\Data\ShippingInformationInterface::class);

        $this->addressValidatorMock->expects($this->exactly(2))
            ->method('validateForCart');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())->method('getShippingMethodCode')->willReturn($shippingMethod);

        $billingAddress = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $addressInformationMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddress);
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn('USA');

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(100);
        $this->quoteMock->expects($this->once())->method('setIsMultiShipping')->with(false)->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('setBillingAddress')->with($billingAddress)->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($this->shippingAddressMock);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock);

        $this->shippingAddressMock->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with($shippingMethod)
            ->willReturn('rates');

        $paymentDetailsMock = $this->createMock(\Magento\Checkout\Api\Data\PaymentDetailsInterface::class);
        $this->paymentDetailsFactoryMock->expects($this->once())->method('create')->willReturn($paymentDetailsMock);

        $paymentMethodMock = $this->createMock(\Magento\Quote\Api\Data\PaymentMethodInterface::class);
        $this->paymentMethodManagementMock->expects($this->once())
            ->method('getList')
            ->with($cartId)
            ->willReturn([$paymentMethodMock]);

        $cartTotalsMock = $this->createMock(\Magento\Quote\Api\Data\TotalsInterface::class);
        $this->cartTotalsRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->willReturn($cartTotalsMock);

        $paymentDetailsMock->expects($this->once())
            ->method('setPaymentMethods')
            ->with([$paymentMethodMock])
            ->willReturnSelf();
        $paymentDetailsMock->expects($this->once())->method('setTotals')->with()->willReturnSelf($cartTotalsMock);

        $this->assertEquals(
            $paymentDetailsMock,
            $this->model->saveAddressInformation($cartId, $addressInformationMock)
        );
    }
}
