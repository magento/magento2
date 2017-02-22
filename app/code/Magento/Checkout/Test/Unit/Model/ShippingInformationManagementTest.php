<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model;

class ShippingInformationManagementTest extends \PHPUnit_Framework_TestCase
{
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
    protected $addressValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsCollectorMock;

    /**
     * @var \Magento\Checkout\Model\ShippingInformationManagement
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->paymentMethodManagementMock = $this->getMock('\Magento\Quote\Api\PaymentMethodManagementInterface');
        $this->paymentDetailsFactoryMock = $this->getMock(
            '\Magento\Checkout\Model\PaymentDetailsFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->cartTotalsRepositoryMock = $this->getMock('\Magento\Quote\Api\CartTotalRepositoryInterface');
        $this->quoteRepositoryMock = $this->getMock('\Magento\Quote\Api\CartRepositoryInterface');
        $this->addressValidatorMock = $this->getMock('\Magento\Quote\Model\QuoteAddressValidator', [], [], '', false);
        $this->loggerMock = $this->getMock('\Psr\Log\LoggerInterface');
        $this->addressRepositoryMock = $this->getMock('\Magento\Customer\Api\AddressRepositoryInterface');
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->totalsCollectorMock =
            $this->getMock('Magento\Quote\Model\Quote\TotalsCollector', [], [], '', false);
        $this->model = $objectManager->getObject(
            '\Magento\Checkout\Model\ShippingInformationManagement',
            [
                'paymentMethodManagement' => $this->paymentMethodManagementMock,
                'paymentDetailsFactory' => $this->paymentDetailsFactoryMock,
                'cartTotalsRepository' => $this->cartTotalsRepositoryMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'addressValidator' => $this->addressValidatorMock,
                'logger' => $this->loggerMock,
                'addressRepository' => $this->addressRepositoryMock,
                'scopeConfig' => $this->scopeConfigMock,
                'totalsCollector' => $this->totalsCollectorMock
            ]
        );

        $this->shippingAddressMock = $this->getMock(
            '\Magento\Quote\Model\Quote\Address',
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
                'getShippingMethod'
            ],
            [],
            '',
            false
        );

        $this->quoteMock = $this->getMock(
            '\Magento\Quote\Model\Quote',
            [
                'isVirtual',
                'getItemsCount',
                'getIsMultiShipping',
                'validateMinimumAmount',
                'getStoreId',
                'setShippingAddress',
                'getShippingAddress',
                'collectTotals'
            ],
            [],
            '',
            false
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart contains virtual product(s) only. Shipping address is not applicable.
     */
    public function testSaveAddressInformationIfCartIsVirtual()
    {
        $cartId = 100;
        $carrierCode = 'carrier_code';
        $shippingMethod = 'shipping_method';
        $addressInformationMock = $this->getMock('\Magento\Checkout\Api\Data\ShippingInformationInterface');

        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())->method('getShippingMethodCode')->willReturn($shippingMethod);

        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(true);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Shipping method is not applicable for empty cart
     */
    public function testSaveAddressInformationIfCartIsEmpty()
    {
        $cartId = 100;
        $carrierCode = 'carrier_code';
        $shippingMethod = 'shipping_method';
        $addressInformationMock = $this->getMock('\Magento\Checkout\Api\Data\ShippingInformationInterface');

        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())->method('getShippingMethodCode')->willReturn($shippingMethod);

        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(0);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Shipping address is not set
     */
    public function testSaveAddressInformationIfShippingAddressNotSet()
    {
        $cartId = 100;
        $carrierCode = 'carrier_code';
        $shippingMethod = 'shipping_method';
        $customerAddressId = 200;
        $addressInformationMock = $this->getMock('\Magento\Checkout\Api\Data\ShippingInformationInterface');

        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())->method('getShippingMethodCode')->willReturn($shippingMethod);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(5);

        $this->shippingAddressMock->expects($this->once())->method('getSaveInAddressBook')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())->method('getSameAsBilling')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn($customerAddressId);

        $this->addressValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->shippingAddressMock)
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($this->shippingAddressMock)
            ->willReturnSelf();
        $this->quoteMock->expects($this->exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $customerAddressMock = $this->getMock('\Magento\Customer\Api\Data\AddressInterface');
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('setSaveInAddressBook')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('setSameAsBilling')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(false);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Unable to save address. Please, check input data.
     */
    public function testSaveAddressInformationThrowExceptionWhileAddressSaving()
    {
        $cartId = 100;
        $carrierCode = 'carrier_code';
        $shippingMethod = 'shipping_method';
        $customerAddressId = 200;
        $exception = new \Exception();

        $addressInformationMock = $this->getMock('\Magento\Checkout\Api\Data\ShippingInformationInterface');
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())->method('getShippingMethodCode')->willReturn($shippingMethod);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(5);

        $this->shippingAddressMock->expects($this->once())->method('getSaveInAddressBook')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())->method('getSameAsBilling')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn($customerAddressId);
        $this->shippingAddressMock->expects($this->once())->method('setSaveInAddressBook')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('setSameAsBilling')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(1);
        $this->totalsCollectorMock
            ->expects($this->once())
            ->method('collectAddressTotals')
            ->willThrowException($exception);
        $this->addressValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->shippingAddressMock)
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($this->shippingAddressMock)
            ->willReturnSelf();
        $this->quoteMock->expects($this->exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $customerAddressMock = $this->getMock('\Magento\Customer\Api\Data\AddressInterface');
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();

        $this->loggerMock->expects($this->once())->method('critical')->with($exception);

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
        $customerAddressId = 200;

        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(5);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $addressInformationMock = $this->getMock('\Magento\Checkout\Api\Data\ShippingInformationInterface');
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())->method('getShippingMethodCode')->willReturn($shippingMethod);

        $this->shippingAddressMock->expects($this->once())->method('getSaveInAddressBook')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())->method('getSameAsBilling')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn($customerAddressId);

        $this->addressValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->shippingAddressMock)
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($this->shippingAddressMock)
            ->willReturnSelf();
        $this->quoteMock->expects($this->exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $customerAddressMock = $this->getMock('\Magento\Customer\Api\Data\AddressInterface');
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('setSaveInAddressBook')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('setSameAsBilling')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(1);
        $this->totalsCollectorMock
            ->expects($this->once())
            ->method('collectAddressTotals')
            ->with($this->quoteMock, $this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with($shippingMethod)
            ->willReturn(false);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Wrong minimum amount.
     */
    public function testSaveAddressInformationIfMinimumAmountIsNotValid()
    {
        $cartId = 100;
        $carrierCode = 'carrier_code';
        $shippingMethod = 'shipping_method';
        $customerAddressId = 200;
        $storeId = 500;
        $minAmountExceptionMessage = __('Wrong minimum amount.');

        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(5);
        $this->quoteMock->expects($this->once())->method('getIsMultiShipping')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('validateMinimumAmount')->with(true)->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $addressInformationMock = $this->getMock('\Magento\Checkout\Api\Data\ShippingInformationInterface');
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())->method('getShippingMethodCode')->willReturn($shippingMethod);

        $this->shippingAddressMock->expects($this->once())->method('getSaveInAddressBook')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())->method('getSameAsBilling')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn($customerAddressId);

        $this->addressValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->shippingAddressMock)
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($this->shippingAddressMock)
            ->willReturnSelf();
        $this->quoteMock->expects($this->exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $customerAddressMock = $this->getMock('\Magento\Customer\Api\Data\AddressInterface');
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('setSaveInAddressBook')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('setSameAsBilling')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(1);
        $this->totalsCollectorMock
            ->expects($this->once())
            ->method('collectAddressTotals')
            ->with($this->quoteMock, $this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with($shippingMethod)
            ->willReturn($this->getMock('\Magento\Quote\Model\Quote\Address\Rate', [], [], '', false));

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('sales/minimum_order/error_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($minAmountExceptionMessage);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Unable to save shipping information. Please, check input data.
     */
    public function testSaveAddressInformationIfCanNotSaveQuote()
    {
        $cartId = 100;
        $carrierCode = 'carrier_code';
        $shippingMethod = 'shipping_method';
        $customerAddressId = 200;
        $exception = new \Exception();

        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(5);
        $this->quoteMock->expects($this->once())->method('getIsMultiShipping')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('validateMinimumAmount')->with(true)->willReturn(true);
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);
        $addressInformationMock = $this->getMock('\Magento\Checkout\Api\Data\ShippingInformationInterface');
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())->method('getShippingMethodCode')->willReturn($shippingMethod);

        $this->shippingAddressMock->expects($this->once())->method('getSaveInAddressBook')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())->method('getSameAsBilling')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn($customerAddressId);

        $this->addressValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->shippingAddressMock)
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($this->shippingAddressMock)
            ->willReturnSelf();
        $this->quoteMock->expects($this->exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $customerAddressMock = $this->getMock('\Magento\Customer\Api\Data\AddressInterface');
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('setSaveInAddressBook')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('setSameAsBilling')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())->method('save')->willReturnSelf();
        $this->totalsCollectorMock
            ->expects($this->once())
            ->method('collectAddressTotals')
            ->with($this->quoteMock, $this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with($shippingMethod)
            ->willReturn($this->getMock('\Magento\Quote\Model\Quote\Address\Rate', [], [], '', false));

        $this->loggerMock->expects($this->once())->method('critical')->with($exception);

        $this->model->saveAddressInformation($cartId, $addressInformationMock);
    }

    public function testSaveAddressInformation()
    {
        $cartId = 100;
        $carrierCode = 'carrier_code';
        $shippingMethod = 'shipping_method';
        $customerAddressId = 200;

        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(5);
        $this->quoteMock->expects($this->once())->method('getIsMultiShipping')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('validateMinimumAmount')->with(true)->willReturn(true);
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock);

        $addressInformationMock = $this->getMock('\Magento\Checkout\Api\Data\ShippingInformationInterface');
        $addressInformationMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $addressInformationMock->expects($this->once())->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->expects($this->once())->method('getShippingMethodCode')->willReturn($shippingMethod);

        $this->shippingAddressMock->expects($this->once())->method('getSaveInAddressBook')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())->method('getSameAsBilling')->willReturn(1);
        $this->shippingAddressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn($customerAddressId);

        $this->addressValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->shippingAddressMock)
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($this->shippingAddressMock)
            ->willReturnSelf();
        $this->quoteMock->expects($this->exactly(2))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $customerAddressMock = $this->getMock('\Magento\Customer\Api\Data\AddressInterface');
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('setSaveInAddressBook')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('setSameAsBilling')->with(1)->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(1);
        $this->totalsCollectorMock
            ->expects($this->once())
            ->method('collectAddressTotals')
            ->with($this->quoteMock, $this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);
        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with($shippingMethod)
            ->willReturn($this->getMock('\Magento\Quote\Model\Quote\Address\Rate', [], [], '', false));

        $paymentDetailsMock = $this->getMock('\Magento\Checkout\Api\Data\PaymentDetailsInterface');
        $this->paymentDetailsFactoryMock->expects($this->once())->method('create')->willReturn($paymentDetailsMock);

        $paymentMethodMock = $this->getMock('\Magento\Quote\Api\Data\PaymentMethodInterface');
        $this->paymentMethodManagementMock->expects($this->once())
            ->method('getList')
            ->with($cartId)
            ->willReturn([$paymentMethodMock]);
        $totalsMock = $this->getMock('\Magento\Quote\Api\Data\TotalsInterface');
        $this->cartTotalsRepositoryMock->expects($this->once())->method('get')->with($cartId)->willReturn($totalsMock);

        $paymentDetailsMock->expects($this->once())
            ->method('setPaymentMethods')
            ->with([$paymentMethodMock])
            ->willReturnSelf();
        $paymentDetailsMock->expects($this->once())->method('setTotals')->with($totalsMock)->willReturnSelf();

        $this->assertEquals(
            $paymentDetailsMock,
            $this->model->saveAddressInformation($cartId, $addressInformationMock)
        );
    }
}
