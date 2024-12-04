<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\PaymentDetailsFactory;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Model\Shipping;
use Magento\Quote\Model\ShippingAssignment;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Checkout\Model\ShippingInformationManagement.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ShippingInformationManagementTest extends TestCase
{
    private const STUB_CART_ID = 100;

    private const STUB_ITEMS_COUNT = 99;

    private const STUB_CARRIER_CODE = 'carrier_code';

    private const STUB_SHIPPING_METHOD = 'shipping_method';

    private const STUB_ERROR_MESSAGE = 'error message';

    /**
     * @var ShippingInformationManagement
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var PaymentMethodManagementInterface|MockObject
     */
    private $paymentMethodManagementMock;

    /**
     * @var PaymentDetailsFactory|MockObject
     */
    private $paymentDetailsFactoryMock;

    /**
     * @var CartTotalRepositoryInterface|MockObject
     */
    private $cartTotalsRepositoryMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var Address|MockObject
     */
    private $shippingAddressMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var ShippingAssignmentFactory|MockObject
     */
    private $shippingAssignmentFactoryMock;

    /**
     * @var CartExtensionFactory|MockObject
     */
    private $cartExtensionFactoryMock;

    /**
     * @var ShippingFactory|MockObject
     */
    private $shippingFactoryMock;

    /**
     * @var CartExtension|MockObject
     */
    private $cartExtensionMock;

    /**
     * @var ShippingAssignment|MockObject
     */
    private $shippingAssignmentMock;

    /**
     * @var QuoteAddressValidator|MockObject
     */
    private $addressValidatorMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->paymentMethodManagementMock = $this->getMockForAbstractClass(PaymentMethodManagementInterface::class);
        $this->paymentDetailsFactoryMock = $this->createPartialMock(
            PaymentDetailsFactory::class,
            ['create']
        );
        $this->cartTotalsRepositoryMock = $this->getMockForAbstractClass(CartTotalRepositoryInterface::class);
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->shippingAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['setShippingAddress', 'getShippingAddress', 'setCollectShippingRates', 'setLimitCarrier'])
            ->onlyMethods(
                [
                    'getSaveInAddressBook',
                    'getSameAsBilling',
                    'getCustomerAddressId',
                    'setSaveInAddressBook',
                    'setSameAsBilling',
                    'getCountryId',
                    'importCustomerAddressData',
                    'save',
                    'getShippingRateByCode',
                    'getShippingMethod'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getIsMultiShipping', 'setIsMultiShipping'])
            ->onlyMethods(
                [
                    'isVirtual',
                    'getItemsCount',
                    'validateMinimumAmount',
                    'getStoreId',
                    'setShippingAddress',
                    'getShippingAddress',
                    'getBillingAddress',
                    'collectTotals',
                    'getExtensionAttributes',
                    'setExtensionAttributes',
                    'setBillingAddress'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->shippingAssignmentFactoryMock = $this->createPartialMock(
            ShippingAssignmentFactory::class,
            ['create']
        );
        $this->cartExtensionFactoryMock = $this->createPartialMock(
            CartExtensionFactory::class,
            ['create']
        );
        $this->shippingFactoryMock = $this->createPartialMock(ShippingFactory::class, ['create']);
        $this->addressValidatorMock = $this->createMock(QuoteAddressValidator::class);

        $this->model = $this->objectManager->getObject(
            ShippingInformationManagement::class,
            [
                'paymentMethodManagement' => $this->paymentMethodManagementMock,
                'paymentDetailsFactory' => $this->paymentDetailsFactoryMock,
                'cartTotalsRepository' => $this->cartTotalsRepositoryMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'shippingAssignmentFactory' => $this->shippingAssignmentFactoryMock,
                'cartExtensionFactory' => $this->cartExtensionFactoryMock,
                'shippingFactory' => $this->shippingFactoryMock,
                'addressValidator' => $this->addressValidatorMock
            ]
        );
    }

    /**
     * Save address with `InputException`
     *
     * @return void
     */
    public function testSaveAddressInformationIfCartIsEmpty(): void
    {
        $cartId = self::STUB_CART_ID;
        /** @var ShippingInformationInterface|MockObject $addressInformationMock */
        $addressInformationMock = $this->getMockForAbstractClass(ShippingInformationInterface::class);

        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(0);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage(
            'The shipping method can\'t be set for an empty cart. Add an item to cart and try again.'
        );
        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * Sets shipping assignments.
     *
     * @param string $shippingMethod
     *
     * @return void
     */
    private function setShippingAssignmentsMocks($shippingMethod): void
    {
        $this->quoteMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);
        $this->shippingAddressMock->expects($this->once())
            ->method('setLimitCarrier');
        $this->cartExtensionMock = $this->getCartExtensionMock();
        $this->cartExtensionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cartExtensionMock);
        $this->cartExtensionMock->expects($this->once())
            ->method('getShippingAssignments')
            ->willReturn(null);

        $this->shippingAssignmentMock = $this->createMock(ShippingAssignment::class);
        $this->shippingAssignmentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->shippingAssignmentMock);
        $this->shippingAssignmentMock->expects($this->once())
            ->method('getShipping')
            ->willReturn(null);

        $shippingMock = $this->createMock(Shipping::class);
        $this->shippingFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($shippingMock);

        $shippingMock->expects($this->once())
            ->method('setAddress')
            ->with($this->shippingAddressMock)
            ->willReturnSelf();
        $shippingMock->expects($this->once())
            ->method('setMethod')
            ->with($shippingMethod)
            ->willReturnSelf();

        $this->shippingAssignmentMock->expects($this->once())
            ->method('setShipping')
            ->with($shippingMock)
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
     * Save address with `StateException`.
     *
     * @return void
     */
    public function testSaveAddressInformationIfShippingAddressNotSet(): void
    {
        $cartId = self::STUB_CART_ID;
        /** @var ShippingInformationInterface|MockObject $addressInformationMock */
        $addressInformationMock = $this->getMockForAbstractClass(ShippingInformationInterface::class);
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn(null);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(self::STUB_ITEMS_COUNT);

        $this->expectException(StateException::class);
        $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');
        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * Save address with `LocalizedException`.
     *
     * @return void
     */
    public function testSaveAddressInformationWithLocalizedException(): void
    {
        $cartId = self::STUB_CART_ID;
        $carrierCode = self::STUB_CARRIER_CODE;
        $shippingMethod = self::STUB_SHIPPING_METHOD;
        $errorMessage = self::STUB_ERROR_MESSAGE;
        $exception = new LocalizedException(__($errorMessage));
        /** @var ShippingInformationInterface|MockObject $addressInformationMock */
        $addressInformationMock = $this->getMockForAbstractClass(ShippingInformationInterface::class);

        $this->addressValidatorMock->expects($this->exactly(2))
            ->method('validateForCart');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())
            ->method('getShippingCarrierCode')
            ->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())
            ->method('getShippingMethodCode')
            ->willReturn($shippingMethod);

        $billingAddress = $this->getMockForAbstractClass(AddressInterface::class);
        $billingAddress->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn(1);

        $addressInformationMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);

        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('USA');

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(self::STUB_ITEMS_COUNT);
        $this->quoteMock->expects($this->once())
            ->method('setIsMultiShipping')
            ->with(false)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('setBillingAddress')
            ->with($billingAddress)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturnSelf();

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'The shipping information was unable to be saved. Error: "' . $errorMessage . '"'
        );
        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * Save address with `InputException`.
     *
     * @return void
     */
    public function testSaveAddressInformationIfCanNotSaveQuote(): void
    {
        $cartId = self::STUB_CART_ID;
        $carrierCode = self::STUB_CARRIER_CODE;
        $shippingMethod = self::STUB_SHIPPING_METHOD;
        /** @var ShippingInformationInterface|MockObject $addressInformationMock */
        $addressInformationMock = $this->getMockForAbstractClass(ShippingInformationInterface::class);

        $this->addressValidatorMock->expects($this->exactly(2))
            ->method('validateForCart');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())
            ->method('getShippingCarrierCode')
            ->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())
            ->method('getShippingMethodCode')
            ->willReturn($shippingMethod);

        $billingAddress = $this->getMockForAbstractClass(AddressInterface::class);
        $addressInformationMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);

        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('USA');

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(self::STUB_ITEMS_COUNT);
        $this->quoteMock->expects($this->once())
            ->method('setIsMultiShipping')
            ->with(false)->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('setBillingAddress')
            ->with($billingAddress)
            ->willReturnSelf();

        $quoteBillingAddress = $this->createMock(Address::class);
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($quoteBillingAddress);

        $quoteShippingAddress = $this->createMock(Address::class);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($quoteShippingAddress);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException(new \Exception());

        $this->expectException(InputException::class);
        $this->expectExceptionMessage(
            'The shipping information was unable to be saved. Verify the input data and try again.'
        );
        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * Save address with `NoSuchEntityException`.
     *
     * @return void
     */
    public function testSaveAddressInformationIfCarrierCodeIsInvalid(): void
    {
        $cartId = self::STUB_CART_ID;
        $carrierCode = self::STUB_CARRIER_CODE;
        $shippingMethod = self::STUB_SHIPPING_METHOD;
        /** @var ShippingInformationInterface|MockObject $addressInformationMock */
        $addressInformationMock = $this->getMockForAbstractClass(ShippingInformationInterface::class);

        $this->addressValidatorMock->expects($this->exactly(2))
            ->method('validateForCart');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())
            ->method('getShippingCarrierCode')
            ->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())
            ->method('getShippingMethodCode')
            ->willReturn($shippingMethod);

        $billingAddress = $this->getMockForAbstractClass(AddressInterface::class);
        $billingAddress->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn(1);

        $addressInformationMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);
        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('USA');

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(self::STUB_ITEMS_COUNT);
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturnSelf();

        $this->quoteMock->expects($this->once())
            ->method('setIsMultiShipping')
            ->with(false)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('setBillingAddress')
            ->with($billingAddress)
            ->willReturnSelf();
        $this->quoteMock->expects($this->exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingMethod')
            ->willReturn($shippingMethod);
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with($shippingMethod)
            ->willReturn(false);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage(
            'Carrier with such method not found: ' . self::STUB_CARRIER_CODE . ', ' . self::STUB_SHIPPING_METHOD
        );

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * Save address info test.
     *
     * @return void
     */
    public function testSaveAddressInformation(): void
    {
        $cartId = self::STUB_CART_ID;
        $carrierCode = self::STUB_CARRIER_CODE;
        $shippingMethod = self::STUB_SHIPPING_METHOD;
        /** @var ShippingInformationInterface|MockObject $addressInformationMock */
        $addressInformationMock = $this->getMockForAbstractClass(ShippingInformationInterface::class);

        $this->addressValidatorMock->expects($this->exactly(2))
            ->method('validateForCart');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())
            ->method('getShippingCarrierCode')
            ->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())
            ->method('getShippingMethodCode')
            ->willReturn($shippingMethod);

        $billingAddress = $this->getMockForAbstractClass(AddressInterface::class);
        $addressInformationMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);
        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('USA');

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(self::STUB_ITEMS_COUNT);
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturnSelf();

        $this->quoteMock->expects($this->once())
            ->method('setIsMultiShipping')
            ->with(false)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('setBillingAddress')
            ->with($billingAddress)
            ->willReturnSelf();
        $this->quoteMock->expects($this->exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingMethod')
            ->willReturn($shippingMethod);
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with($shippingMethod)
            ->willReturn('rates');

        $paymentDetailsMock = $this->getMockForAbstractClass(PaymentDetailsInterface::class);
        $this->paymentDetailsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($paymentDetailsMock);

        $paymentMethodMock = $this->getMockForAbstractClass(PaymentMethodInterface::class);
        $this->paymentMethodManagementMock->expects($this->once())
            ->method('getList')
            ->with($cartId)
            ->willReturn([$paymentMethodMock]);

        $cartTotalsMock = $this->getMockForAbstractClass(TotalsInterface::class);
        $this->cartTotalsRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->willReturn($cartTotalsMock);

        $paymentDetailsMock->expects($this->once())
            ->method('setPaymentMethods')
            ->with([$paymentMethodMock])
            ->willReturnSelf();
        $paymentDetailsMock->expects($this->once())
            ->method('setTotals')
            ->willReturn($cartTotalsMock);

        $this->assertEquals(
            $paymentDetailsMock,
            $this->model->saveAddressInformation($cartId, $addressInformationMock)
        );
    }

    /**
     * Build cart extension mock.
     *
     * @return MockObject
     */
    private function getCartExtensionMock(): MockObject
    {
        $mockBuilder = $this->getMockBuilder(CartExtension::class);
        try {
            $mockBuilder->addMethods(['getShippingAssignments', 'setShippingAssignments']);
        } catch (RuntimeException $e) {
            // CartExtension already generated.
        }

        return $mockBuilder->getMock();
    }
}
