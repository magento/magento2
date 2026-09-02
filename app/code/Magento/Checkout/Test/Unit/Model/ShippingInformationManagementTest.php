<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\PaymentDetailsFactory;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Checkout\Model\AddressComparatorInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Api\Data\AddressSearchResultsInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Shipping;
use Magento\Quote\Model\ShippingAssignment;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Quote\Model\Quote\TotalsCollector;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Model\QuoteAddressValidationService;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Api\Data\CartExtensionInterface;

/**
 * Test for \Magento\Checkout\Model\ShippingInformationManagement.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ShippingInformationManagementTest extends TestCase
{
    use MockCreationTrait;
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
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var TotalsCollector|MockObject
     */
    private $totalsCollectorMock;

    /**
     * @var AddressComparatorInterface|MockObject
     */
    private $addressComparatorMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private $customerAddressRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->paymentMethodManagementMock = $this->createMock(PaymentMethodManagementInterface::class);
        $this->paymentDetailsFactoryMock = $this->createMock(PaymentDetailsFactory::class);
        $this->cartTotalsRepositoryMock = $this->createMock(CartTotalRepositoryInterface::class);
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->shippingAddressMock = $this->createPartialMockWithReflection(
            Address::class,
            ['getCountryId', 'getShippingMethod', 'getShippingRateByCode']
        );
        $this->shippingAddressMock->method('getCountryId')->willReturn('US');

        $this->quoteMock = $this->createMock(Quote::class);

        $this->shippingAssignmentFactoryMock = $this->createMock(ShippingAssignmentFactory::class);
        $this->cartExtensionFactoryMock = $this->createMock(CartExtensionFactory::class);
        $this->shippingFactoryMock = $this->createMock(ShippingFactory::class);
        $this->addressValidatorMock = $this->createMock(QuoteAddressValidator::class);

        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->addressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->totalsCollectorMock = $this->createMock(TotalsCollector::class);
        $this->addressComparatorMock = $this->createMock(AddressComparatorInterface::class);
        $quoteAddressValidationServiceMock = $this->createMock(QuoteAddressValidationService::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->customerAddressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);

        $this->model = new ShippingInformationManagement(
            $this->paymentMethodManagementMock,
            $this->paymentDetailsFactoryMock,
            $this->cartTotalsRepositoryMock,
            $this->quoteRepositoryMock,
            $this->addressValidatorMock,
            $this->loggerMock,
            $this->addressRepositoryMock,
            $this->scopeConfigMock,
            $this->totalsCollectorMock,
            $this->cartExtensionFactoryMock,
            $this->shippingAssignmentFactoryMock,
            $this->shippingFactoryMock,
            $this->addressComparatorMock,
            $quoteAddressValidationServiceMock,
            $this->searchCriteriaBuilderMock,
            $this->customerAddressRepositoryMock
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
        $addressInformationMock = $this->createMock(ShippingInformationInterface::class);

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
        $this->quoteMock->method('getExtensionAttributes')->willReturn(null);
        $this->cartExtensionMock = $this->createPartialMockWithReflection(
            CartExtensionInterface::class,
            ['setShippingAssignments', 'getShippingAssignments']
        );
        $this->cartExtensionMock->method('setShippingAssignments')->willReturnSelf();
        $this->cartExtensionMock->method('getShippingAssignments')->willReturn([]);
        $this->cartExtensionFactoryMock->method('create')->willReturn($this->cartExtensionMock);

        $this->shippingAssignmentMock = $this->createMock(ShippingAssignment::class);
        $this->shippingAssignmentFactoryMock->method('create')->willReturn($this->shippingAssignmentMock);
        $this->shippingAssignmentMock->method('getShipping')->willReturn(null);

        $shippingMock = $this->createMock(Shipping::class);
        $this->shippingFactoryMock->method('create')->willReturn($shippingMock);

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

        $this->quoteMock->method('setExtensionAttributes')->willReturnSelf();
    }

    /**
     * Save address with `StateException`.
     *
     * @return void
     */
    public function testSaveAddressInformationIfShippingAddressNotSet(): void
    {
        $cartId = self::STUB_CART_ID;
        
        $addressMock = $this->createMock(Address::class);
        $addressMock->method('getCountryId')->willReturn(null);
        
        /** @var ShippingInformationInterface|MockObject $addressInformationMock */
        $addressInformationMock = $this->createMock(ShippingInformationInterface::class);
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($addressMock);

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
        $addressInformationMock = $this->createMock(ShippingInformationInterface::class);

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

        $billingAddress = $this->createMock(AddressInterface::class);
        $billingAddress->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn(1);

        $addressInformationMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);

        $this->shippingAddressMock->setCountryIdVal('USA');

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(self::STUB_ITEMS_COUNT);
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
        $addressInformationMock = $this->createMock(ShippingInformationInterface::class);

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

        $billingAddress = $this->createMock(AddressInterface::class);
        $addressInformationMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);

        $this->shippingAddressMock->setCountryIdVal('USA');

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(self::STUB_ITEMS_COUNT);
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
        $addressInformationMock = $this->createMock(ShippingInformationInterface::class);

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

        $billingAddress = $this->createMock(AddressInterface::class);
        $billingAddress->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn(1);

        $addressInformationMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);
        $this->shippingAddressMock->setCountryIdVal('USA');

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(self::STUB_ITEMS_COUNT);
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
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

        $this->shippingAddressMock->setShippingMethodVal($shippingMethod);
        $this->shippingAddressMock->setShippingRateByCodeVal(false);

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveAddressInformation(): void
    {
        $cartId = self::STUB_CART_ID;
        $carrierCode = self::STUB_CARRIER_CODE;
        $shippingMethod = self::STUB_SHIPPING_METHOD;
        /** @var ShippingInformationInterface|MockObject $addressInformationMock */
        $addressInformationMock = $this->createMock(ShippingInformationInterface::class);

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

        $billingAddress = $this->createMock(AddressInterface::class);
        $addressInformationMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);
        $this->shippingAddressMock->setCountryIdVal('USA');

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(self::STUB_ITEMS_COUNT);
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')
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

        $this->shippingAddressMock->method('getShippingMethod')->willReturn($shippingMethod);
        $this->shippingAddressMock->method('getShippingRateByCode')->with($shippingMethod)->willReturn('rates');

        $paymentDetailsMock = $this->createMock(PaymentDetailsInterface::class);
        $this->paymentDetailsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($paymentDetailsMock);

        $paymentMethodMock = $this->createMock(PaymentMethodInterface::class);
        $this->paymentMethodManagementMock->expects($this->once())
            ->method('getList')
            ->with($cartId)
            ->willReturn([$paymentMethodMock]);

        $cartTotalsMock = $this->createMock(TotalsInterface::class);
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
     * A new address equal to one already in the address book is linked to it instead of being duplicated
     *
     * @return void
     */
    public function testSaveAddressInformationReusesMatchingCustomerAddress(): void
    {
        $cartId = self::STUB_CART_ID;
        $carrierCode = self::STUB_CARRIER_CODE;
        $shippingMethod = self::STUB_SHIPPING_METHOD;
        $existingAddressId = 7;

        // The address the customer typed into the "Add new address" form. The postcode arrives as an
        // integer, which is what a REST payload delivers, and the street carries a trailing empty line.
        $this->shippingAddressMock = $this->createPartialMockWithReflection(
            Address::class,
            [
                'getCountryId',
                'getShippingMethod',
                'getShippingRateByCode',
                'getCustomerAddressId',
                'setCustomerAddressId',
                'getSaveInAddressBook',
                'setSaveInAddressBook',
                'setLimitCarrier',
                'getPrefix',
                'getFirstname',
                'getMiddlename',
                'getLastname',
                'getSuffix',
                'getCompany',
                'getStreet',
                'getCity',
                'getRegionId',
                'getRegion',
                'getPostcode',
                'getTelephone',
                'getFax',
                'getVatId',
            ]
        );
        $this->shippingAddressMock->method('getCountryId')->willReturn('US');
        $this->shippingAddressMock->method('getSaveInAddressBook')->willReturn(1);
        $this->shippingAddressMock->method('getFirstname')->willReturn('John');
        $this->shippingAddressMock->method('getLastname')->willReturn('Doe');
        $this->shippingAddressMock->method('getStreet')->willReturn(['Green str, 67', '']);
        $this->shippingAddressMock->method('getCity')->willReturn('CityM');
        $this->shippingAddressMock->method('getRegionId')->willReturn(1);
        $this->shippingAddressMock->method('getRegion')->willReturn(null);
        $this->shippingAddressMock->method('getPostcode')->willReturn(75477);
        $this->shippingAddressMock->method('getTelephone')->willReturn('3468676');
        // Empty before the lookup, resolved to the existing entry afterwards.
        $this->shippingAddressMock->method('getCustomerAddressId')
            ->willReturnOnConsecutiveCalls(null, $existingAddressId);

        // The very same address, as it is already stored in the address book. It differs only in the
        // way the equal values are represented: string postcode, no trailing street line, upper case.
        $regionMock = $this->createMock(RegionInterface::class);
        $regionMock->method('getRegion')->willReturn('Alabama');
        $existingAddressMock = $this->createMock(CustomerAddressInterface::class);
        $existingAddressMock->method('getId')->willReturn($existingAddressId);
        $existingAddressMock->method('getFirstname')->willReturn('John');
        $existingAddressMock->method('getLastname')->willReturn('Doe');
        $existingAddressMock->method('getStreet')->willReturn(['Green Str, 67']);
        $existingAddressMock->method('getCity')->willReturn('CityM');
        $existingAddressMock->method('getCountryId')->willReturn('US');
        $existingAddressMock->method('getRegionId')->willReturn(1);
        $existingAddressMock->method('getRegion')->willReturn($regionMock);
        $existingAddressMock->method('getPostcode')->willReturn('75477');
        $existingAddressMock->method('getTelephone')->willReturn('3468676');

        $searchResultsMock = $this->createMock(AddressSearchResultsInterface::class);
        $searchResultsMock->method('getItems')->willReturn([$existingAddressMock]);
        $this->searchCriteriaBuilderMock->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilderMock->method('create')
            ->willReturn($this->createMock(SearchCriteria::class));
        $this->customerAddressRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn($searchResultsMock);

        // The existing entry is reused and nothing is written to the address book.
        $this->shippingAddressMock->expects($this->once())
            ->method('setCustomerAddressId')
            ->with($existingAddressId);
        $this->shippingAddressMock->expects($this->once())
            ->method('setSaveInAddressBook')
            ->with(0);

        $quoteShippingAddressMock = $this->createPartialMockWithReflection(
            Address::class,
            ['getCustomerAddressId', 'getShippingMethod', 'getShippingRateByCode']
        );
        $quoteShippingAddressMock->method('getCustomerAddressId')->willReturn(null);
        $quoteShippingAddressMock->method('getShippingMethod')->willReturn($shippingMethod);
        $quoteShippingAddressMock->method('getShippingRateByCode')->with($shippingMethod)->willReturn('rates');

        /** @var ShippingInformationInterface|MockObject $addressInformationMock */
        $addressInformationMock = $this->createMock(ShippingInformationInterface::class);
        $addressInformationMock->method('getShippingAddress')->willReturn($this->shippingAddressMock);
        $addressInformationMock->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->method('getShippingMethodCode')->willReturn($shippingMethod);
        $addressInformationMock->method('getBillingAddress')->willReturn(null);

        // getCustomerId() is served by the magic getter of the model, so it has to be declared explicitly.
        $this->quoteMock = $this->createPartialMockWithReflection(
            Quote::class,
            [
                'getItemsCount',
                'getCustomerId',
                'getShippingAddress',
                'setIsMultiShipping',
                'getIsVirtual',
                'getExtensionAttributes',
                'setExtensionAttributes',
            ]
        );
        $this->quoteRepositoryMock->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->method('getItemsCount')->willReturn(self::STUB_ITEMS_COUNT);
        $this->quoteMock->method('getCustomerId')->willReturn(42);
        $this->quoteMock->method('getIsVirtual')->willReturn(false);
        $this->quoteMock->method('getShippingAddress')->willReturn($quoteShippingAddressMock);

        $this->setShippingAssignmentsMocks($carrierCode . '_' . $shippingMethod);

        $paymentDetailsMock = $this->createMock(PaymentDetailsInterface::class);
        $this->paymentDetailsFactoryMock->method('create')->willReturn($paymentDetailsMock);
        $this->paymentMethodManagementMock->method('getList')->with($cartId)->willReturn([]);
        $this->cartTotalsRepositoryMock->method('get')->with($cartId)
            ->willReturn($this->createMock(TotalsInterface::class));

        $this->assertSame(
            $paymentDetailsMock,
            $this->model->saveAddressInformation($cartId, $addressInformationMock)
        );
    }
}
