<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model;

use Magento\Braintree\Model\PaymentMethod;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory as TransactionCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use \Braintree_Result_Successful;
use \Braintree_Result_Error;
use \Braintree_Transaction;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;

/**
 * Class PaymentMethodTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentMethodTest extends \PHPUnit_Framework_TestCase
{
    const CHANNEL = 'Magento Community Edition 2.0';
    const PAYMENT_METHOD_NONCE = 'nonce';
    const CC_TOKEN = 'cc45kn';
    const MERCHANT_ACCOUNT_ID = '5z4rh64p772cy7pb';
    const FNAME = 'John';
    const LNAME = 'Doe';
    const AUTH_TRAN_ID = 'r4z34j';
    const AUTH_AMOUNT = 5.76;
    const TOTAL_AMOUNT = 10.02;
    const AUTH_CC_LAST_4 = '0004';
    const CUSTOMER_ID = '221b3649effb4bb1b62fc940691bd18c';

    /**
     * @var \Magento\Braintree\Model\PaymentMethod
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Braintree\Model\Config\Cc|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Braintree\Model\Vault|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $vaultMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $infoInstanceMock;

    /**
     * @var TransactionCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesTransactionCollectionFactoryMock;

    /**
     * @var \Magento\Payment\Model\Method\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Braintree\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Directory\Model\RegionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionFactoryMock;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMetaDataMock;

    /**
     * @var \Magento\Braintree\Helper\Error|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $errorHelperMock;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $psrLoggerMock;

    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeTransaction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreeTransactionMock;

    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeCreditCard|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreeCreditCardMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appStateMock;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepository;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('\Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->getMock();
        $this->vaultMock = $this->getMockBuilder('\Magento\Braintree\Model\Vault')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder('\Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->salesTransactionCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productMetaDataMock = $this->getMockBuilder('\Magento\Framework\App\ProductMetadataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder('\Magento\Braintree\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->errorHelperMock = $this->getMockBuilder('\Magento\Braintree\Helper\Error')
            ->disableOriginalConstructor()
            ->getMock();
        $this->regionFactoryMock = $this->getMockBuilder('\Magento\Directory\Model\RegionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('\Magento\Payment\Model\Method\Logger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeTransactionMock = $this->getMockBuilder(
            '\Magento\Braintree\Model\Adapter\BraintreeTransaction'
        )->getMock();
        $this->braintreeCreditCardMock = $this->getMockBuilder(
            '\Magento\Braintree\Model\Adapter\BraintreeCreditCard'
        )->getMock();
        $this->appStateMock = $this->getMockBuilder('\Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();
        $this->psrLoggerMock = $this->getMock('\\Psr\Log\LoggerInterface');
        $this->contextMock->expects($this->any())
            ->method('getLogger')
            ->willReturn($this->psrLoggerMock);
        $this->contextMock->expects($this->any())
            ->method('getAppState')
            ->willReturn($this->appStateMock);
        $this->orderRepository = $this->getMockBuilder('Magento\Sales\Api\OrderRepositoryInterface')
            ->getMockForAbstractClass();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Model\PaymentMethod',
            [
                'context' => $this->contextMock,
                'config' => $this->configMock,
                'vault' => $this->vaultMock,
                'registry' => $this->registryMock,
                'salesTransactionCollectionFactory' => $this->salesTransactionCollectionFactoryMock,
                'productMetaData' => $this->productMetaDataMock,
                'braintreeHelper' => $this->helperMock,
                'errorHelper' => $this->errorHelperMock,
                'regionFactory' => $this->regionFactoryMock,
                'logger' => $this->loggerMock,
                'braintreeTransaction' => $this->braintreeTransactionMock,
                'braintreeCreditCard' => $this->braintreeCreditCardMock,
                'orderRepository' => $this->orderRepository
            ]
        );
        $this->infoInstanceMock = $this->getMockBuilder(InfoInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setCcType',
                    'setCcOwner',
                    'setCcLast4',
                    'setCcNumber',
                    'setCcCid',
                    'setCcExpMonth',
                    'setCcExpYear',
                    'setCcSsIssue',
                    'setCcSsStartMonth',
                    'setCcSsStartYear',
                    'getOrder',
                    'getQuote',
                    'getCcType'
                ]
            )->getMockForAbstractClass();
        $this->productMetaDataMock->expects($this->any())
            ->method('getEdition')
            ->willReturn('Community Edition');
        $this->productMetaDataMock->expects($this->any())
            ->method('getVersion')
            ->willReturn('2.0');
    }

    public function testAssignData()
    {
        $ccType = 'VI';
        $ccExpMonth = '10';
        $ccExpYear = '2020';

        $ccLast4 = '9216';
        $ccToken = 'erf6re';
        $paymentMethodNonce = 'nonce';
        $storeInVault = true;
        $deviceData = 'mobile';
        $data = [
            'additional_data' => [
                'cc_type' => $ccType,
                'cc_exp_month' => $ccExpMonth,
                'cc_exp_year' => $ccExpYear,
                'cc_last4' => $ccLast4,
                'cc_token' => $ccToken,
                'payment_method_nonce' => $paymentMethodNonce,
                'store_in_vault' => $storeInVault,
                'device_data' => $deviceData
            ]
        ];
        $data = new \Magento\Framework\DataObject($data);
        $this->model->setInfoInstance($this->infoInstanceMock);
        $this->configMock->expects($this->once())
            ->method('getConfigData')
            ->with('fraudprotection')
            ->willReturn(1);

        $this->infoInstanceMock->expects($this->once())
            ->method('setCcType')
            ->with($ccType)
            ->willReturnSelf();
        $this->infoInstanceMock->expects($this->once())
            ->method('setCcLast4')
            ->with($ccLast4)
            ->willReturnSelf();
        $this->infoInstanceMock->expects($this->once())
            ->method('setCcExpMonth')
            ->with($ccExpMonth)
            ->willReturnSelf();
        $this->infoInstanceMock->expects($this->once())
            ->method('setCcExpYear')
            ->with($ccExpYear)
            ->willReturnSelf();

        $this->infoInstanceMock->expects($this->at(0))
            ->method('setAdditionalInformation')
            ->with('device_data', $deviceData);

        $this->infoInstanceMock->expects($this->at(1))
            ->method('setAdditionalInformation')
            ->with('cc_last4', $ccLast4);

        $this->infoInstanceMock->expects($this->at(2))
            ->method('setAdditionalInformation')
            ->with('cc_token', $ccToken);

        $this->infoInstanceMock->expects($this->at(3))
            ->method('setAdditionalInformation')
            ->with('store_in_vault', $storeInVault);

        $this->infoInstanceMock->expects($this->at(4))
            ->method('setAdditionalInformation')
            ->with('payment_method_nonce', $paymentMethodNonce);

        $this->model->assignData($data);
    }

    //Start: test validate
    /**
     * @param $countryId
     * @param $ccType
     * @param null $ccToken
     */
    protected function setupInfoInstance($countryId, $ccType, $ccToken = null)
    {
        $quoteObj = new \Magento\Framework\DataObject(
            [
                'billing_address' => new \Magento\Framework\DataObject(
                    [
                        'country_id' => $countryId,
                    ]
                ),
            ]
        );
        $this->infoInstanceMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteObj);

        if ($ccType) {
            $this->infoInstanceMock->expects($this->once())
                ->method('getCcType')
                ->willReturn($ccType);
        }
        if ($ccToken) {
            $this->infoInstanceMock->expects($this->any())
                ->method('getAdditionalInformation')
                ->with('cc_token')
                ->willReturn($ccToken);
        }
        return;
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Selected payment type is not allowed for billing country.
     */
    public function testValidateInvalidCountry()
    {
        $countryId = 'US';
        $this->setupInfoInstance($countryId, null);

        $this->model->setInfoInstance($this->infoInstanceMock);

        $this->configMock->expects($this->once())
            ->method('canUseForCountry')
            ->with($countryId)
            ->willReturn(false);
        $this->model->validate();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Credit card type is not allowed for your country.
     */
    public function testValidateInvalidCardForCountry()
    {
        $countryId = 'US';
        $ccType = "VI";
        $errorMsg = new \Magento\Framework\Phrase("Credit card type is not allowed for your country.");

        $this->setupInfoInstance($countryId, $ccType, null);

        $this->model->setInfoInstance($this->infoInstanceMock);

        $this->configMock->expects($this->once())
            ->method('canUseForCountry')
            ->with($countryId)
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('canUseCcTypeForCountry')
            ->with($countryId, $ccType)
            ->willReturn($errorMsg);
        $this->model->validate();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Credit card type is not allowed for your country.
     */
    public function testValidateInvalidCardForCountryFromToken()
    {
        $countryId = 'US';
        $ccType = "VI";
        $ccToken = "fet4df";
        $errorMsg = new \Magento\Framework\Phrase("Credit card type is not allowed for your country.");

        $this->setupInfoInstance($countryId, null, $ccToken);

        $this->model->setInfoInstance($this->infoInstanceMock);

        $this->configMock->expects($this->once())
            ->method('canUseForCountry')
            ->with($countryId)
            ->willReturn(true);

        $this->vaultMock->expects($this->once())
            ->method('getSavedCardType')
            ->with($ccToken)
            ->willReturn($ccType);

        $this->configMock->expects($this->once())
            ->method('canUseCcTypeForCountry')
            ->with($countryId, $ccType)
            ->willReturn($errorMsg);
        $this->model->validate();
    }

    public function testValidateValidCardForCountryFromToken()
    {
        $countryId = 'US';
        $ccType = "VI";
        $ccToken = "fet4df";

        $this->setupInfoInstance($countryId, null, $ccToken);

        $this->model->setInfoInstance($this->infoInstanceMock);

        $this->configMock->expects($this->once())
            ->method('canUseForCountry')
            ->with($countryId)
            ->willReturn(true);

        $this->vaultMock->expects($this->once())
            ->method('getSavedCardType')
            ->with($ccToken)
            ->willReturn($ccType);

        $this->configMock->expects($this->once())
            ->method('canUseCcTypeForCountry')
            ->with($countryId, $ccType)
            ->willReturn(false);
        $this->assertEquals($this->model, $this->model->validate());
    }

    public function testValidate()
    {
        $countryId = 'US';
        $ccType = "VI";

        $this->setupInfoInstance($countryId, $ccType, null);

        $this->model->setInfoInstance($this->infoInstanceMock);

        $this->configMock->expects($this->once())
            ->method('canUseForCountry')
            ->with($countryId)
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('canUseCcTypeForCountry')
            ->with($countryId, $ccType)
            ->willReturn(null);
        $this->assertEquals($this->model, $this->model->validate());
    }
    //END: test validate

    //Start: test authorize
    protected function setupOrderMock(
        $billingAddress,
        $shippingAddress,
        $customerEmail,
        $orderId,
        $customerId,
        $storeId
    ) {
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getIncrementId',
                    'getBillingAddress',
                    'getShippingAddress',
                    'getCustomerEmail',
                    'getCustomerId',
                    'getStoreId',
                    'getBaseTotalDue'
                ]
            )->getMock();

        $orderMock->expects($this->any())
            ->method('getIncrementId')
            ->willReturn($orderId);
        $orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);
        $orderMock->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($shippingAddress);
        $orderMock->expects($this->any())
            ->method('getCustomerEmail')
            ->willReturn($customerEmail);
        $orderMock->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $orderMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $orderMock->expects(static::any())
            ->method('getBaseTotalDue')
            ->willReturn(self::TOTAL_AMOUNT);

        $this->orderRepository->expects(static::any())
            ->method('get')
            ->willReturn($orderMock);

        return $orderMock;
    }

    /**
     * @param \Magento\Framework\DataObject $paymentObject
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setupPaymentObject(\Magento\Framework\DataObject $paymentObject, $storeId)
    {
        $customerId = '12';
        $customerEmail = 'abc@example.com';
        $company = 'NA';
        $phone = '3316655';
        $fax = '3316677';
        $orderId = '100000024';
        $street = '1201 N 1st Stree';
        $street2 = 'build 45';
        $city = 'San Jose';
        $region = 'California';
        $regionCode = 'CA';
        $regionId = 65;
        $postcode = '63241';
        $countryId = 'US';

        $addressData = [
            'firstname' => self::FNAME,
            'lastname' => self::LNAME,
            'company' => $company,
            'telephone' => $phone,
            'fax' => $fax,
            'street' => [$street, $street2],
            'city' => $city,
            'region' => $region,
            'region_id' => $regionId,
            'postcode' => $postcode,
            'country_id' => $countryId,
            'address_type' => 'billing',
        ];

        $billingAddress = new \Magento\Framework\DataObject($addressData);
        $addressData['address_type'] = 'shipping';
        $shippingAddress = new \Magento\Framework\DataObject($addressData);

        $order = $this->setupOrderMock(
            $billingAddress,
            $shippingAddress,
            $customerEmail,
            $orderId,
            $customerId,
            $storeId
        );

        $paymentObject->setOrder($order);
        $this->helperMock->expects($this->once())
            ->method('generateCustomerId')
            ->with($customerId, $customerEmail)
            ->willReturn(self::CUSTOMER_ID);

        $regionMock = $this->getMockBuilder('Magento\Directory\Model\Region')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCode', 'load'])
            ->getMock();
        $regionMock->expects($this->any())
            ->method('getId')
            ->willReturn($regionId);
        $regionMock->expects($this->any())
            ->method('getCode')
            ->willReturn($regionCode);
        $regionMock->expects($this->any())
            ->method('load')
            ->with($regionId)
            ->willReturnSelf();
        $this->regionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($regionMock);

        $braintreeAddressData = [
            'firstName' => self::FNAME,
            'lastName' => self::LNAME,
            'company' => $company,
            'streetAddress' => $street,
            'extendedAddress' => $street2,
            'locality' => $city,
            'region' => $regionCode,
            'postalCode' => $postcode,
            'countryCodeAlpha2' => $countryId,
        ];
        return [
            'channel' => self::CHANNEL,
            'orderId' => $orderId,
            'customer' => [
                'firstName' => self::FNAME,
                'lastName' => self::LNAME,
                'company' => $company,
                'phone' => $phone,
                'fax' => $fax,
                'email' => $customerEmail,
            ],
            'billing' => $braintreeAddressData,
            'shipping' => $braintreeAddressData,
        ];
    }

    /**
     * @param array $actual
     * @param array $expected
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function compareArray(array $actual, array $expected)
    {
        foreach ($actual as $key => $value) {
            if (!isset($expected[$key])) {
                $this->assertTrue(false, 'Array mismatch for key: ' . $key);
            }
            if (is_array($value)) {
                if (!is_array($expected[$key])) {
                    $this->assertTrue(false, 'Array mismatch for key: ' . $key);
                }
                if (!$this->compareArray($value, $expected[$key])) {
                    $this->assertTrue(false, 'Array mismatch for key: ' . $key);
                }
            } else {
                if ($value != $expected[$key]) {
                    $this->assertTrue(false, 'Array mismatch for key: ' . $key);
                }
            }
        }
        foreach (array_keys($expected) as $key) {
            if (isset($expected[$key]) && !array_key_exists($key, $actual)) {
                $this->assertTrue(false, 'Array mismatch for key: ' . $key);
            }
        }
        return true;
    }

    protected function setupAuthorizeRequest(
        array $configData,
        $vault,
        $registry,
        $existingCustomer,
        array $paymentInfo,
        array $expectedRequestAttributes,
        $paymentObject,
        $storeId,
        $amount
    ) {
        //setup general payment and order information
        $transactionRequest = $this->setupPaymentObject(
            $paymentObject,
            $storeId
        );

        //setup config options
        foreach ($configData as $methodName => $value) {
            $this->configMock->expects($this->any())
                ->method($methodName)
                ->willReturn($value);
        }

        //setup payment info instance
        $this->infoInstanceMock->expects($this->any())
            ->method('getAdditionalInformation')
            ->willReturnMap($paymentInfo);
        $this->model->setInfoInstance($this->infoInstanceMock);

        $expectedRequestAttribs = array_merge($transactionRequest, $expectedRequestAttributes);
        $expectedRequestAttribs['amount'] = $amount;

        if ($existingCustomer !== null) {
            $this->vaultMock->expects($this->once())
                ->method('exists')
                ->with(self::CUSTOMER_ID)
                ->willReturn($existingCustomer);
            if ($existingCustomer) {
                unset($expectedRequestAttribs['customer']);
            }
        }

        if (!empty($vault['canSaveCard'])) {
            $this->vaultMock->expects($this->once())
                ->method('canSaveCard')
                ->with(self::AUTH_CC_LAST_4)
                ->willReturn($vault['canSaveCard']);
        }

        if (array_key_exists('registry', $registry)) {
            $this->registryMock->expects($this->once())
                ->method('registry')
                ->with(PaymentMethod::REGISTER_NAME)
                ->willReturn($registry['registry']);
        }
        if (array_key_exists('register', $registry)) {
            $this->registryMock->expects($this->once())
                ->method('register')
                ->with(PaymentMethod::REGISTER_NAME, true);
        }
        return $expectedRequestAttribs;
    }

    /**
     * @param array $response
     */
    protected function setupSuccessResponse(array $response)
    {
        $params = array_keys($response);
        $values = array_values($response);
        $result = new \Braintree_Result_Successful($values, $params);
        return $result;
    }

    /**
     * @param array $configData
     * @param mixed $vault
     * @param mixed $registry
     * @param bool $existingCustomer
     * @param array $paymentInfo
     * @param array $expectedRequestAttributes
     * @param array $braintreeResponse
     * @param array $expectedPaymentFields
     * @param string $appState
     * @dataProvider authorizeDataProvider
     */
    public function testAuthorizeSuccess(
        array $configData,
        $vault,
        $registry,
        $existingCustomer,
        array $paymentInfo,
        array $expectedRequestAttributes,
        array $braintreeResponse,
        array $expectedPaymentFields,
        $appState = null
    ) {
        $storeId = 3;
        $amount = self::AUTH_AMOUNT;
        $currencyMock = $this->getPriceCurrencyMock();
        /** @var \Magento\Sales\Model\Order\Payment $paymentObject */
        $paymentObject = $this->objectManagerHelper->getObject('Magento\Sales\Model\Order\Payment', [
            'priceCurrency' => $currencyMock
        ]);

        $expectedRequestAttribs = $this->setupAuthorizeRequest(
            $configData,
            $vault,
            $registry,
            $existingCustomer,
            $paymentInfo,
            $expectedRequestAttributes,
            $paymentObject,
            $storeId,
            $amount
        );
        //setup braintree response
        $result = $this->setupSuccessResponse($braintreeResponse);
        $this->braintreeTransactionMock->expects($this->once())
            ->method('sale')
            ->with($this->callback(function ($actual) use ($expectedRequestAttribs) {
                return $this->compareArray($actual, $expectedRequestAttribs);
            }))
            ->willReturn($result);

        $this->psrLoggerMock->expects($this->never())
            ->method('critical');

        if ($appState) {
            $this->appStateMock->expects($this->any())
                ->method('getAreaCode')
                ->willReturn($appState);
        }

        $paymentObject->setParentId('1');

        $this->assertEquals($this->model, $this->model->authorize($paymentObject, $amount));
        foreach ($expectedPaymentFields as $key => $value) {
            if ($key == 'getTransactionAdditionalInfo') {
                $this->assertEquals($value, $paymentObject->getTransactionAdditionalInfo());
            } else {
                $this->assertEquals($value, $paymentObject->getData($key), 'Incorrect field in paymentobject: ' . $key);
            }
        }
        $this->assertEquals($storeId, $this->model->getStore());
        $this->assertEquals(PaymentMethod::STATUS_APPROVED, $paymentObject->getStatus());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function authorizeDataProvider()
    {
        return [
            'payment_nonce_no_vault' => [
                'config' => [
                    'is3dSecureEnabled' => false,
                    'isFraudProtectionEnabled' => false,
                    'isDebugEnabled' => false,
                    'useVault' => false,
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [],
                'registry' => [],
                'existing_customer' => null,
                'infoInstanceValueMap' => [
                    ['store_in_vault', null],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', null],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'options' => [
                        'addBillingAddressToPaymentMethod' => true,
                    ],
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodNonce' => self::PAYMENT_METHOD_NONCE,
                    'creditCard' => [
                        'cardholderName' => self::FNAME . ' ' . self::LNAME,
                    ],
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'creditCard' => [
                                'token' => null,
                                'last4' => self::AUTH_CC_LAST_4,
                                'expirationMonth' => '10',
                                'expirationYear' => '2020',
                                'bin' => 'bin',
                            ],
                            'avsErrorResponseCode' => null,
                            'avsPostalCodeResponseCode' => 'M',
                            'avsStreetAddressResponseCode' => 'M',
                            'cvvResponseCode' => 'M',
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'cc_last_4' => self::AUTH_CC_LAST_4,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'avsPostalCodeResponseCode' => 'M',
                        'avsStreetAddressResponseCode' => 'M',
                        'cvvResponseCode' => 'M',
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                    ],
                ],
            ],
            'payment_nonce_with_vault_existing_customer_no_save' => [
                'config' => [
                    'is3dSecureEnabled' => false,
                    'isFraudProtectionEnabled' => false,
                    'isDebugEnabled' => false,
                    'useVault' => true, //vault is enabled
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [],
                'registry' => [],
                'existing_customer' => true, //existing customer
                'infoInstanceValueMap' => [
                    ['store_in_vault', null],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', null],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'customerId' => self::CUSTOMER_ID,
                    'options' => [
                        'storeInVault' => false, //not storing in vault
                        'addBillingAddressToPaymentMethod' => true,
                    ],
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodNonce' => self::PAYMENT_METHOD_NONCE,
                    'creditCard' => [
                        'cardholderName' => self::FNAME . ' ' . self::LNAME,
                    ],
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'creditCard' => [
                                'token' => null,
                                'last4' => self::AUTH_CC_LAST_4,
                                'expirationMonth' => 10,
                                'expirationYear' => 2020,
                                'bin' => 'bin',
                            ],
                            'avsErrorResponseCode' => null,
                            'avsPostalCodeResponseCode' => 'M',
                            'avsStreetAddressResponseCode' => 'M',
                            'cvvResponseCode' => 'M',
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'cc_last_4' => self::AUTH_CC_LAST_4,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'avsPostalCodeResponseCode' => 'M',
                        'avsStreetAddressResponseCode' => 'M',
                        'cvvResponseCode' => 'M',
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                    ],
                ],
            ],
            'payment_nonce_with_vault_existing_customer_save' => [
                'config' => [
                    'is3dSecureEnabled' => false,
                    'isFraudProtectionEnabled' => false,
                    'isDebugEnabled' => false,
                    'useVault' => true, //vault is enabled
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [
                    'canSaveCard' => true,
                ],
                'registry' => [
                    'registry' => null, //return null in registry call
                    'register' => true, //call register method
                ],
                'existing_customer' => true, //existing customer
                'infoInstanceValueMap' => [
                    ['store_in_vault', 1],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', null],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'customerId' => self::CUSTOMER_ID,
                    'options' => [
                        'storeInVaultOnSuccess' => true, //save in vault
                        'addBillingAddressToPaymentMethod' => true,
                    ],
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodNonce' => self::PAYMENT_METHOD_NONCE,
                    'creditCard' => [
                        'cardholderName' => self::FNAME . ' ' . self::LNAME,
                    ],
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'creditCard' => [
                                'token' => null,
                                'last4' => self::AUTH_CC_LAST_4,
                                'expirationMonth' => 10,
                                'expirationYear' => 2020,
                                'bin' => 'bin',
                            ],
                            'avsErrorResponseCode' => null,
                            'avsPostalCodeResponseCode' => 'M',
                            'avsStreetAddressResponseCode' => 'M',
                            'cvvResponseCode' => 'M',
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'cc_last_4' => self::AUTH_CC_LAST_4,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'avsPostalCodeResponseCode' => 'M',
                        'avsStreetAddressResponseCode' => 'M',
                        'cvvResponseCode' => 'M',
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                    ],
                ],
            ],
            'payment_nonce_with_vault_existing_customer_save_dup' => [
                'config' => [
                    'is3dSecureEnabled' => false,
                    'isFraudProtectionEnabled' => false,
                    'isDebugEnabled' => false,
                    'useVault' => true, //vault is enabled
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [
                    'canSaveCard' => false, //duplicate card
                ],
                'registry' => [
                    'registry' => null, //return null in registry call
                ],
                'existing_customer' => true, //existing customer
                'infoInstanceValueMap' => [
                    ['store_in_vault', 1],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', null],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'customerId' => self::CUSTOMER_ID,
                    'options' => [
                        'addBillingAddressToPaymentMethod' => true,
                    ],
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodNonce' => self::PAYMENT_METHOD_NONCE,
                    'creditCard' => [
                        'cardholderName' => self::FNAME . ' ' . self::LNAME,
                    ],
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'creditCard' => [
                                'token' => null,
                                'last4' => self::AUTH_CC_LAST_4,
                                'expirationMonth' => 10,
                                'expirationYear' => 2020,
                                'bin' => 'bin',
                            ],
                            'avsErrorResponseCode' => null,
                            'avsPostalCodeResponseCode' => 'M',
                            'avsStreetAddressResponseCode' => 'M',
                            'cvvResponseCode' => 'M',
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'cc_last_4' => self::AUTH_CC_LAST_4,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'avsPostalCodeResponseCode' => 'M',
                        'avsStreetAddressResponseCode' => 'M',
                        'cvvResponseCode' => 'M',
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                    ],
                ],
            ],
            'payment_nonce_with_vault_existing_customer_save_3dsecure' => [
                'config' => [
                    'is3dSecureEnabled' => true,
                    'isFraudProtectionEnabled' => false,
                    'isDebugEnabled' => false,
                    'useVault' => true, //vault is enabled
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [
                    'canSaveCard' => true,
                ],
                'registry' => [
                    'registry' => null, //return null in registry call
                    'register' => true, //call register method
                ],
                'existing_customer' => true, //existing customer
                'infoInstanceValueMap' => [
                    ['store_in_vault', 1],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', null],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'customerId' => self::CUSTOMER_ID,
                    'options' => [
                        'storeInVaultOnSuccess' => true, //save in vault
                        'addBillingAddressToPaymentMethod' => true,
                        'three_d_secure' => [ //3dsecure option
                            'required' => true,
                        ]
                    ],
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodNonce' => self::PAYMENT_METHOD_NONCE,
                    'creditCard' => [
                        'cardholderName' => self::FNAME . ' ' . self::LNAME,
                    ],
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'creditCard' => [
                                'token' => null,
                                'last4' => self::AUTH_CC_LAST_4,
                                'expirationMonth' => 10,
                                'expirationYear' => 2020,
                                'bin' => 'bin',
                            ],
                            'avsErrorResponseCode' => null,
                            'avsPostalCodeResponseCode' => 'M',
                            'avsStreetAddressResponseCode' => 'M',
                            'cvvResponseCode' => 'M',
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'cc_last_4' => self::AUTH_CC_LAST_4,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'avsPostalCodeResponseCode' => 'M',
                        'avsStreetAddressResponseCode' => 'M',
                        'cvvResponseCode' => 'M',
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                    ],
                ],
            ],
            'payment_nonce_with_vault_existing_customer_save_3dsecure_backend' => [
                'config' => [
                    'is3dSecureEnabled' => true, //this will be ignored for backend
                    'isFraudProtectionEnabled' => false,
                    'isDebugEnabled' => false,
                    'useVault' => true, //vault is enabled
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [
                    'canSaveCard' => true,
                ],
                'registry' => [
                    'registry' => null, //return null in registry call
                    'register' => true, //call register method
                ],
                'existing_customer' => true, //existing customer
                'infoInstanceValueMap' => [
                    ['store_in_vault', 1],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', null],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'customerId' => self::CUSTOMER_ID,
                    'options' => [
                        'storeInVaultOnSuccess' => true, //save in vault
                        'addBillingAddressToPaymentMethod' => true,
                    ],
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodNonce' => self::PAYMENT_METHOD_NONCE,
                    'creditCard' => [
                        'cardholderName' => self::FNAME . ' ' . self::LNAME,
                    ],
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'creditCard' => [
                                'token' => null,
                                'last4' => self::AUTH_CC_LAST_4,
                                'expirationMonth' => 10,
                                'expirationYear' => 2020,
                                'bin' => 'bin',
                            ],
                            'avsErrorResponseCode' => null,
                            'avsPostalCodeResponseCode' => 'M',
                            'avsStreetAddressResponseCode' => 'M',
                            'cvvResponseCode' => 'M',
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'cc_last_4' => self::AUTH_CC_LAST_4,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'avsPostalCodeResponseCode' => 'M',
                        'avsStreetAddressResponseCode' => 'M',
                        'cvvResponseCode' => 'M',
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                    ],
                ],
                'appState' => 'adminhtml',
            ],
            'payment_nonce_no_vault_fraud_protection' => [
                'config' => [
                    'is3dSecureEnabled' => false,
                    'isFraudProtectionEnabled' => true,
                    'isDebugEnabled' => false,
                    'useVault' => false,
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [],
                'registry' => [],
                'existing_customer' => null,
                'infoInstanceValueMap' => [
                    ['store_in_vault', null],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', null],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'options' => [
                        'addBillingAddressToPaymentMethod' => true,
                    ],
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodNonce' => self::PAYMENT_METHOD_NONCE,
                    'creditCard' => [
                        'cardholderName' => self::FNAME . ' ' . self::LNAME,
                    ],
                    'deviceData' => 'fraud_detection_data',
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'creditCard' => [
                                'token' => null,
                                'last4' => self::AUTH_CC_LAST_4,
                                'expirationMonth' => '10',
                                'expirationYear' => '2020',
                                'bin' => 'bin',
                            ],
                            'avsErrorResponseCode' => null,
                            'avsPostalCodeResponseCode' => 'M',
                            'avsStreetAddressResponseCode' => 'M',
                            'cvvResponseCode' => 'M',
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'cc_last_4' => self::AUTH_CC_LAST_4,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'avsPostalCodeResponseCode' => 'M',
                        'avsStreetAddressResponseCode' => 'M',
                        'cvvResponseCode' => 'M',
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                    ],
                ],
            ],
            'token' => [
                'config' => [
                    'is3dSecureEnabled' => false,
                    'isFraudProtectionEnabled' => true,
                    'isDebugEnabled' => false,
                    'useVault' => true,
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [],
                'registry' => [],
                'existing_customer' => null,
                'infoInstanceValueMap' => [
                    ['store_in_vault', null],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', self::CC_TOKEN],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodToken' => self::CC_TOKEN,
                    'customerId' => self::CUSTOMER_ID,
                    'deviceData' => 'fraud_detection_data',
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'creditCard' => [
                                'token' => self::CC_TOKEN,
                                'last4' => self::AUTH_CC_LAST_4,
                                'expirationMonth' => '10',
                                'expirationYear' => '2020',
                                'bin' => 'bin',
                            ],
                            'avsErrorResponseCode' => null,
                            'avsPostalCodeResponseCode' => 'M',
                            'avsStreetAddressResponseCode' => 'M',
                            'cvvResponseCode' => 'M',
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'cc_last_4' => self::AUTH_CC_LAST_4,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'avsPostalCodeResponseCode' => 'M',
                        'avsStreetAddressResponseCode' => 'M',
                        'cvvResponseCode' => 'M',
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                    ],
                    'getTransactionAdditionalInfo' => ['token' => self::CC_TOKEN]
                ],
            ],
            'token_with_3dsecure' => [
                'config' => [
                    'is3dSecureEnabled' => true,
                    'isFraudProtectionEnabled' => true,
                    'isDebugEnabled' => false,
                    'useVault' => true,
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [],
                'registry' => [],
                'existing_customer' => null,
                'infoInstanceValueMap' => [
                    ['store_in_vault', null],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', self::CC_TOKEN],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodNonce' => self::PAYMENT_METHOD_NONCE,
                    'customerId' => self::CUSTOMER_ID,
                    'deviceData' => 'fraud_detection_data',
                    'options' => [
                        'three_d_secure' => [
                            'required' => true,
                        ]
                    ]
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'creditCard' => [
                                'token' => self::CC_TOKEN,
                                'last4' => self::AUTH_CC_LAST_4,
                                'expirationMonth' => '10',
                                'expirationYear' => '2020',
                                'bin' => 'bin',
                            ],
                            'avsErrorResponseCode' => null,
                            'avsPostalCodeResponseCode' => 'M',
                            'avsStreetAddressResponseCode' => 'M',
                            'cvvResponseCode' => 'M',
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'cc_last_4' => self::AUTH_CC_LAST_4,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'avsPostalCodeResponseCode' => 'M',
                        'avsStreetAddressResponseCode' => 'M',
                        'cvvResponseCode' => 'M',
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                    ],
                    'getTransactionAdditionalInfo' => ['token' => self::CC_TOKEN]
                ],
            ],
            'token_with_3dsecure_backend' => [
                'config' => [
                    'is3dSecureEnabled' => true, //this will be ignored for backend
                    'isFraudProtectionEnabled' => true,
                    'isDebugEnabled' => false,
                    'useVault' => true,
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [],
                'registry' => [],
                'existing_customer' => null,
                'infoInstanceValueMap' => [
                    ['store_in_vault', null],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', self::CC_TOKEN],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodToken' => self::CC_TOKEN,
                    'customerId' => self::CUSTOMER_ID,
                    'deviceData' => 'fraud_detection_data',
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'creditCard' => [
                                'token' => self::CC_TOKEN,
                                'last4' => self::AUTH_CC_LAST_4,
                                'expirationMonth' => '10',
                                'expirationYear' => '2020',
                                'bin' => 'bin',
                            ],
                            'avsErrorResponseCode' => null,
                            'avsPostalCodeResponseCode' => 'M',
                            'avsStreetAddressResponseCode' => 'M',
                            'cvvResponseCode' => 'M',
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'cc_last_4' => self::AUTH_CC_LAST_4,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'avsPostalCodeResponseCode' => 'M',
                        'avsStreetAddressResponseCode' => 'M',
                        'cvvResponseCode' => 'M',
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                    ],
                    'getTransactionAdditionalInfo' => ['token' => self::CC_TOKEN]
                ],
                'appState' => 'adminhtml',
            ],
        ];
    }

    /**
     * @param array $configData
     * @param mixed $vault
     * @param mixed $registry
     * @param bool $existingCustomer
     * @param array $paymentInfo
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage error
     */
    public function testAuthorizeCaughtLocalizedException()
    {
        $exception = new \Magento\Framework\Exception\LocalizedException(
            new \Magento\Framework\Phrase('error')
        );
        $paymentObjectMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->setMethods(['getOrder'])
            ->getMock();

        $paymentObjectMock->expects($this->once())
            ->method('getOrder')
            ->willThrowException($exception);

        $this->psrLoggerMock->expects($this->never())
            ->method('critical');

        $this->registryMock->expects($this->once())
            ->method('unregister')
            ->with(PaymentMethod::REGISTER_NAME);

        $this->model->authorize($paymentObjectMock, 1);
    }

    /**
     * @param array $configData
     * @param mixed $vault
     * @param mixed $registry
     * @param bool $existingCustomer
     * @param array $paymentInfo
     * @dataProvider authorizeFailureProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage error
     */
    public function testAuthorizeError(array $configData, $vault, $registry, $existingCustomer, array $paymentInfo)
    {
        $storeId = 3;
        $amount = self::AUTH_AMOUNT;
        $paymentObject = $this->objectManagerHelper->getObject('Magento\Sales\Model\Order\Payment');

        $this->setupAuthorizeRequest(
            $configData,
            $vault,
            $registry,
            $existingCustomer,
            $paymentInfo,
            [],
            $paymentObject,
            $storeId,
            $amount
        );

        //setup braintree response
        $result = $this->getMockBuilder('\Braintree_Result_Error')
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeTransactionMock->expects($this->once())
            ->method('sale')
            ->willReturn($result);

        $this->psrLoggerMock->expects($this->never())
            ->method('critical');

        $this->errorHelperMock->expects($this->once())
            ->method('parseBraintreeError')
            ->with($result)
            ->willReturn(new \Magento\Framework\Phrase('error'));
        $this->model->authorize($paymentObject, $amount);
    }

    /**
     * @param array $configData
     * @param mixed $vault
     * @param mixed $registry
     * @param bool $existingCustomer
     * @param array $paymentInfo
     * @dataProvider authorizeFailureProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please try again later
     */
    public function testAuthorizeException(array $configData, $vault, $registry, $existingCustomer, array $paymentInfo)
    {
        $storeId = 3;
        $amount = self::AUTH_AMOUNT;
        $paymentObject = $this->objectManagerHelper->getObject('Magento\Sales\Model\Order\Payment');

        $this->setupAuthorizeRequest(
            $configData,
            $vault,
            $registry,
            $existingCustomer,
            $paymentInfo,
            [],
            $paymentObject,
            $storeId,
            $amount
        );

        $exception = new \Exception("error");
        $this->psrLoggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->braintreeTransactionMock->expects($this->once())
            ->method('sale')
            ->willThrowException($exception);

        $this->model->authorize($paymentObject, $amount);
    }

    public function authorizeFailureProvider()
    {
        return [
            'payment_nonce_no_vault' => [
                'config' => [
                    'is3dSecureEnabled' => false,
                    'isFraudProtectionEnabled' => false,
                    'isDebugEnabled' => false,
                    'useVault' => false,
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [],
                'registry' => [],
                'existing_customer' => null,
                'infoInstanceValueMap' => [
                    ['store_in_vault', null],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', null],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
            ],
        ];
    }
    //End: test authorize

    //Start: test capture
    /**
     * @param int $paymentId
     * @param int $numberOfTransactions
     * @return $this
     */
    protected function setupSalesTransaction($paymentId, $numberOfTransactions)
    {
        $transactionCollectionMock = $this->getMockBuilder(
            'Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection'
        )->disableOriginalConstructor()
            ->getMock();
        $transactionCollectionMock->expects($this->at(0))
            ->method('addFieldToFilter')
            ->with('payment_id', $paymentId)
            ->willReturnSelf();
        $transactionCollectionMock->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('txn_type', PaymentTransaction::TYPE_CAPTURE)
            ->willReturnSelf();
        $transactionCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($numberOfTransactions);
        $this->salesTransactionCollectionFactoryMock->expects($this->at(0))
            ->method('create')
            ->willReturn($transactionCollectionMock);
        return $this;
    }

    protected function setupPaymentObjectForCapture($paymentId)
    {
        $order = $this->getMockBuilder('Magento\Sales\Api\Data\OrderInterface')
            ->getMockForAbstractClass();
        $order->expects(static::any())
            ->method('getBaseTotalDue')
            ->willReturn(self::TOTAL_AMOUNT);
        $this->orderRepository->expects(static::any())
            ->method('get')
            ->willReturn($order);

        $currencyMock = $this->getPriceCurrencyMock();

        $paymentObject = $this->objectManagerHelper->getObject(
            'Magento\Sales\Model\Order\Payment',
            [
                'priceCurrency' => $currencyMock,
                'orderRepository' => $this->orderRepository,
                'data' => [
                    'id' => $paymentId,
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                ]
            ]
        );

        return $paymentObject;
    }

    /**
     * @covers \Magento\Braintree\Model\PaymentMethod::capture()
     * @throws LocalizedException
     */
    public function testCaptureSuccess()
    {
        $amount = self::AUTH_AMOUNT;
        $paymentId = 1005;

        $paymentObject = $this->setupPaymentObjectForCapture($paymentId);
        $this->setupSalesTransaction($paymentId, 0); //no existing capture transaction

        $successResult = $this->setupSuccessResponse([]);
        $this->braintreeTransactionMock->expects($this->once())
            ->method('submitForSettlement')
            ->with(self::AUTH_TRAN_ID, $amount)
            ->willReturn($successResult);

        $this->psrLoggerMock->expects($this->never())
            ->method('critical');

        $paymentObject->setParentId('1');

        $this->model->capture($paymentObject, $amount);
        $this->assertEquals(0, $paymentObject->getIsTransactionClosed());
        $this->assertFalse($paymentObject->getShouldCloseParentTransaction());
    }

    /**
     * @covers \Magento\Braintree\Model\PaymentMethod::capture()
     * @return void
     */
    public function testCaptureSuccessAuthTransactionClosed()
    {
        $paymentId = 31232;
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->setupPaymentObjectForCapture($paymentId);
        $this->setupSalesTransaction($paymentId, 0); //no existing capture transaction

        $result = $this->setupSuccessResponse([]);
        $this->braintreeTransactionMock->expects(static::once())
            ->method('submitForSettlement')
            ->with(self::AUTH_TRAN_ID, self::TOTAL_AMOUNT)
            ->willReturn($result);

        $this->psrLoggerMock->expects(static::never())
            ->method('critical');

        $payment->setParentId(1);
        $this->model->capture($payment, self::TOTAL_AMOUNT);

        static::assertFalse($payment->getIsTransactionClosed());
        static::assertTrue($payment->getShouldCloseParentTransaction());

    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage There was an error capturing the transaction: error.
     */
    public function testCaptureError()
    {
        $amount = self::AUTH_AMOUNT;
        $paymentId = 1005;

        $paymentObject = $this->setupPaymentObjectForCapture($paymentId);
        $this->setupSalesTransaction($paymentId, 0); //no existing capture transaction

        //setup braintree error response
        $resultError = $this->getMockBuilder('\Braintree_Result_Error')
            ->disableOriginalConstructor()
            ->getMock();

        $this->errorHelperMock->expects($this->once())
            ->method('parseBraintreeError')
            ->with($resultError)
            ->willReturn(new \Magento\Framework\Phrase('error'));
        $this->braintreeTransactionMock->expects($this->once())
            ->method('submitForSettlement')
            ->with(self::AUTH_TRAN_ID, $amount)
            ->willReturn($resultError);

        $this->psrLoggerMock->expects($this->once())
            ->method('critical');

        $this->model->capture($paymentObject, $amount);
    }

    protected function setupAuthTransaction($paymentId, $authTransaction)
    {
        $authTransactionCollectionMock = $this->getMockBuilder(
            'Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection'
        )->disableOriginalConstructor()
            ->getMock();
        $authTransactionCollectionMock->expects($this->once())
            ->method('addPaymentIdFilter')
            ->with($paymentId)
            ->willReturnSelf();
        $authTransactionCollectionMock->expects($this->once())
            ->method('addTxnTypeFilter')
            ->with(PaymentTransaction::TYPE_AUTH)
            ->willReturnSelf();
        $authTransactionCollectionMock->expects($this->any())
            ->method('setOrder')
            ->willReturnSelf();
        $authTransactionCollectionMock->expects($this->any())
            ->method('setPageSize')
            ->with(1)
            ->willReturnSelf();
        $authTransactionCollectionMock->expects($this->any())
            ->method('setCurPage')
            ->with(1)
            ->willReturnSelf();
        $authTransactionCollectionMock->expects($this->any())
            ->method('getFirstItem')
            ->willReturn($authTransaction);

        $this->salesTransactionCollectionFactoryMock->expects($this->at(1))
            ->method('create')
            ->willReturn($authTransactionCollectionMock);
        return $this;
    }

    /**
     * @param array $configData
     * @param mixed $vault
     * @param mixed $registry
     * @param bool $existingCustomer
     * @param array $paymentInfo
     * @param array $braintreeResponse
     * @dataProvider partialCaptureDataProvider
     */
    public function testPartialCaptureWithToken(
        array $configData,
        $vault,
        $registry,
        $existingCustomer,
        array $paymentInfo,
        array $braintreeResponse
    ) {
        $amount = self::AUTH_AMOUNT;
        $paymentId = 1005;
        $authTransactionId = 1006;
        $storeId = 2;

        $paymentObject = $this->setupPaymentObjectForCapture($paymentId);
        $this->setupSalesTransaction($paymentId, 1); //one existing capture transaction

        $authTransactionMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $authTransactionMock->expects($this->any())
            ->method('getId')
            ->willReturn($authTransactionId);
        $authTransactionMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('token')
            ->willReturn(self::CC_TOKEN);
        $this->setupAuthTransaction($paymentId, $authTransactionMock);

        $this->braintreeCreditCardMock->expects($this->once())
            ->method('find')
            ->with(self::CC_TOKEN)
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('initEnvironment')
            ->with($storeId);

        $this->appStateMock->expects($this->any())
            ->method('getAreaCode')
            ->willReturn(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        //set up authorize
        $this->setupAuthorizeRequest(
            $configData,
            $vault,
            $registry,
            $existingCustomer,
            $paymentInfo,
            [],
            $paymentObject,
            $storeId,
            $amount
        );
        //setup braintree response
        $result = $this->setupSuccessResponse($braintreeResponse);
        $this->braintreeTransactionMock->expects($this->once())
            ->method('sale')
            ->willReturn($result);

        $paymentObject->setParentId('1');

        $this->model->capture($paymentObject, $amount);
        $this->assertEquals(0, $paymentObject->getIsTransactionClosed());
        $this->assertFalse($paymentObject->getShouldCloseParentTransaction());
    }

    /**
     * @param array $configData
     * @param mixed $vault
     * @param mixed $registry
     * @param bool $existingCustomer
     * @param array $paymentInfo
     * @dataProvider partialCaptureDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage There was an error capturing the transaction: Please try again later.
     */
    public function testPartialCaptureWithTokenException(
        array $configData,
        $vault,
        $registry,
        $existingCustomer,
        array $paymentInfo
    ) {
        $amount = self::AUTH_AMOUNT;
        $paymentId = 1005;
        $authTransactionId = 1006;
        $storeId = 2;

        $paymentObject = $this->setupPaymentObjectForCapture($paymentId);
        $this->setupSalesTransaction($paymentId, 1); //one existing capture transaction

        $authTransactionMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $authTransactionMock->expects($this->any())
            ->method('getId')
            ->willReturn($authTransactionId);
        $authTransactionMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('token')
            ->willReturn(self::CC_TOKEN);
        $this->setupAuthTransaction($paymentId, $authTransactionMock);

        $this->braintreeCreditCardMock->expects($this->once())
            ->method('find')
            ->with(self::CC_TOKEN)
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('initEnvironment')
            ->with($storeId);

        //set up authorize
        $this->setupAuthorizeRequest(
            $configData,
            $vault,
            $registry,
            $existingCustomer,
            $paymentInfo,
            [],
            $paymentObject,
            $storeId,
            $amount
        );
        $this->braintreeTransactionMock->expects($this->once())
            ->method('sale')
            ->willThrowException(new \Exception('error'));

        $this->model->capture($paymentObject, $amount);
    }

    public function partialCaptureDataProvider()
    {
        return [
            'token_3dsecure_enabled' => [
                'config' => [
                    'is3dSecureEnabled' => true, //3dsecure will be ignored for partial capture with vault
                    'isFraudProtectionEnabled' => true,
                    'isDebugEnabled' => false,
                    'useVault' => true,
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'vault' => [],
                'registry' => [],
                'existing_customer' => null,
                'infoInstanceValueMap' => [
                    ['store_in_vault', null],
                    ['cc_last4', self::AUTH_CC_LAST_4],
                    ['cc_token', self::CC_TOKEN],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'creditCard' => [
                                'token' => self::CC_TOKEN,
                                'last4' => self::AUTH_CC_LAST_4,
                                'expirationMonth' => '10',
                                'expirationYear' => '2020',
                                'bin' => 'bin',
                            ],
                            'avsErrorResponseCode' => null,
                            'avsPostalCodeResponseCode' => 'M',
                            'avsStreetAddressResponseCode' => 'M',
                            'cvvResponseCode' => 'M',
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                        ]
                    ),
                ],
            ],
        ];
    }

    public function testPartialCaptureCloneTransaction()
    {
        $amount = self::AUTH_AMOUNT;
        $paymentId = 1005;
        $authTransactionId = 1006;
        $braintreeTransactionId = '4fg7hj';

        $paymentObject = $this->setupPaymentObjectForCapture($paymentId);
        $this->setupSalesTransaction($paymentId, 1); //one existing capture transaction

        $authTransactionMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $authTransactionMock->expects($this->any())
            ->method('getId')
            ->willReturn($authTransactionId);
        $authTransactionMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('token')
            ->willReturn(self::CC_TOKEN);
        $authTransactionMock->expects($this->once())
            ->method('getTxnId')
            ->willReturn($braintreeTransactionId);
        $this->setupAuthTransaction($paymentId, $authTransactionMock);

        $this->braintreeCreditCardMock->expects($this->once())
            ->method('find')
            ->with(self::CC_TOKEN)
            ->willThrowException(new \Exception('not found'));

        $braintreeResultData = [
            'transaction' => \Braintree_Transaction::factory(
                [
                    'id' => self::AUTH_TRAN_ID,
                    'creditCard' => [
                        'token' => null,
                        'last4' => self::AUTH_CC_LAST_4,
                        'expirationMonth' => '10',
                        'expirationYear' => '2020',
                        'bin' => 'bin',
                    ],
                    'avsErrorResponseCode' => null,
                    'avsPostalCodeResponseCode' => 'M',
                    'avsStreetAddressResponseCode' => 'M',
                    'cvvResponseCode' => 'M',
                    'gatewayRejectionReason' => null,
                    'processorAuthorizationCode' => 'S02T5Q',
                    'processorResponseCode' => '1000',
                    'processorResponseText' => 'Approved',
                ]
            ),
        ];
        $resultSuccess = $this->setupSuccessResponse($braintreeResultData);
        $this->braintreeTransactionMock->expects($this->once())
            ->method('cloneTransaction')
            ->with(
                $braintreeTransactionId,
                [
                    'amount' => $amount,
                    'options' => [
                        'submitForSettlement' => true,
                    ],
                ]
            )
            ->willReturn($resultSuccess);

        $paymentObject->setParentId('1');

        $this->model->capture($paymentObject, $amount);
        $this->assertEquals(PaymentMethod::STATUS_APPROVED, $paymentObject->getStatus());
        $this->assertEquals($amount, $paymentObject->getAmount());
        $this->assertEquals(self::AUTH_TRAN_ID, $paymentObject->getCcTransId());
        $this->assertEquals(self::AUTH_CC_LAST_4, $paymentObject->getCcLast4());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage There was an error capturing the transaction: error.
     */
    public function testPartialCaptureCloneTransactionError()
    {
        $amount = self::AUTH_AMOUNT;
        $paymentId = 1005;
        $authTransactionId = 1006;
        $braintreeTransactionId = '4fg7hj';

        $paymentObject = $this->setupPaymentObjectForCapture($paymentId);
        $this->setupSalesTransaction($paymentId, 1); //one existing capture transaction

        $authTransactionMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $authTransactionMock->expects($this->any())
            ->method('getId')
            ->willReturn($authTransactionId);
        $authTransactionMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('token')
            ->willReturn(self::CC_TOKEN);
        $authTransactionMock->expects($this->once())
            ->method('getTxnId')
            ->willReturn($braintreeTransactionId);
        $this->setupAuthTransaction($paymentId, $authTransactionMock);

        $this->braintreeCreditCardMock->expects($this->once())
            ->method('find')
            ->with(self::CC_TOKEN)
            ->willThrowException(new \Exception('not found'));

        $resultError = $this->getMockBuilder('\Braintree_Result_Error')
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeTransactionMock->expects($this->once())
            ->method('cloneTransaction')
            ->with(
                $braintreeTransactionId,
                [
                    'amount' => $amount,
                    'options' => [
                        'submitForSettlement' => true,
                    ],
                ]
            )->willReturn($resultError);

        $this->errorHelperMock->expects($this->once())
            ->method('parseBraintreeError')
            ->with($resultError)
            ->willReturn(new \Magento\Framework\Phrase('error'));
        $this->model->capture($paymentObject, $amount);
    }
    //End: test capture

    //Start: test refund
    protected function setupPaymentObjectForRefund($refundTransactionId)
    {
        $paymentObject = $this->objectManagerHelper->getObject(
            'Magento\Sales\Model\Order\Payment',
            [
                'data' => [
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'refund_transaction_id' => $refundTransactionId,
                ]
            ]
        );

        return $paymentObject;
    }

    /**
     * Refund will be handled as void status is either AUTHORIZED or SUBMITTED_FOR_SETTLEMENT
     *
     * @dataProvider refundDataProvider
     */
    public function testRefundWithVoid($status)
    {
        $refundTransactionId = 'refundId';
        $amount = self::AUTH_AMOUNT;

        $paymentObject = $this->setupPaymentObjectForRefund($refundTransactionId);
        $this->helperMock->expects($this->once())
            ->method('clearTransactionId')
            ->with($refundTransactionId)
            ->willReturn($refundTransactionId);

        $transaction = \Braintree_Transaction::factory(
            [
                'id' => self::AUTH_TRAN_ID,
                'status' => $status,
                'amount' => $amount,
            ]
        );

        $this->braintreeTransactionMock->expects($this->once())
            ->method('find')
            ->with($refundTransactionId)
            ->willReturn($transaction);

        $resultSuccess = $this->setupSuccessResponse([]);
        $this->braintreeTransactionMock->expects($this->once())
            ->method('void')
            ->with($refundTransactionId)
            ->willReturn($resultSuccess);
        $this->braintreeTransactionMock->expects($this->never())
            ->method('refund');

        $this->model->refund($paymentObject, $amount);
        $this->assertEquals(1, $paymentObject->getIsTransactionClosed());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage There was an error refunding the transaction: error.
     * @dataProvider refundDataProvider
     */
    public function testRefundWithVoidError($status)
    {
        $refundTransactionId = 'refundId';
        $amount = self::AUTH_AMOUNT;

        $paymentObject = $this->setupPaymentObjectForRefund($refundTransactionId);
        $this->helperMock->expects($this->once())
            ->method('clearTransactionId')
            ->with($refundTransactionId)
            ->willReturn($refundTransactionId);

        $transaction = \Braintree_Transaction::factory(
            [
                'id' => self::AUTH_TRAN_ID,
                'status' => $status,
                'amount' => $amount,
            ]
        );

        $this->braintreeTransactionMock->expects($this->once())
            ->method('find')
            ->with($refundTransactionId)
            ->willReturn($transaction);

        $resultError = $this->getMockBuilder('\Braintree_Result_Error')
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeTransactionMock->expects($this->once())
            ->method('void')
            ->with($refundTransactionId)
            ->willReturn($resultError);
        $this->braintreeTransactionMock->expects($this->never())
            ->method('refund');

        $this->errorHelperMock->expects($this->once())
            ->method('parseBraintreeError')
            ->with($resultError)
            ->willReturn(new \Magento\Framework\Phrase('error'));
        $this->model->refund($paymentObject, $amount);
    }

    public function refundDataProvider()
    {
        return [
            'authorized' => [
                'status' => \Braintree_Transaction::AUTHORIZED,
            ],
            'submitted_for_settlement' => [
                'status' => \Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT
            ],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessageRegExp /There was an error refunding the transaction: This refund is for a partial/
     */
    public function testPartialRefundNotSettled()
    {
        $refundTransactionId = 'refundId';
        $amount = self::AUTH_AMOUNT;

        $paymentObject = $this->setupPaymentObjectForRefund($refundTransactionId);
        $this->helperMock->expects($this->once())
            ->method('clearTransactionId')
            ->with($refundTransactionId)
            ->willReturn($refundTransactionId);

        $transaction = \Braintree_Transaction::factory(
            [
                'id' => self::AUTH_TRAN_ID,
                'status' => \Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT,
                'amount' => $amount,
            ]
        );

        $this->braintreeTransactionMock->expects($this->once())
            ->method('find')
            ->with($refundTransactionId)
            ->willReturn($transaction);

        $resultSuccess = $this->setupSuccessResponse([]);
        $this->braintreeTransactionMock->expects($this->never())
            ->method('void')
            ->with($refundTransactionId)
            ->willReturn($resultSuccess);

        $this->model->refund($paymentObject, $amount - 1);
        $this->assertEquals(1, $paymentObject->getIsTransactionClosed());
    }

    public function testRefund()
    {
        $refundTransactionId = 'refundId';
        $amount = self::AUTH_AMOUNT;

        $paymentObject = $this->setupPaymentObjectForRefund($refundTransactionId);
        $this->helperMock->expects($this->once())
            ->method('clearTransactionId')
            ->with($refundTransactionId)
            ->willReturn($refundTransactionId);

        $transaction = \Braintree_Transaction::factory(
            [
                'id' => self::AUTH_TRAN_ID,
                'status' => \Braintree_Transaction::SETTLED,
                'amount' => $amount,
            ]
        );

        $this->braintreeTransactionMock->expects($this->once())
            ->method('find')
            ->with($refundTransactionId)
            ->willReturn($transaction);

        $resultSuccess = $this->setupSuccessResponse([]);
        $this->braintreeTransactionMock->expects($this->once())
            ->method('refund')
            ->with($refundTransactionId, $amount)
            ->willReturn($resultSuccess);
        $this->braintreeTransactionMock->expects($this->never())
            ->method('void');

        $this->model->refund($paymentObject, $amount);
        $this->assertEquals(1, $paymentObject->getIsTransactionClosed());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage There was an error refunding the transaction: error.
     */
    public function testRefundError()
    {
        $refundTransactionId = 'refundId';
        $amount = self::AUTH_AMOUNT;

        $paymentObject = $this->setupPaymentObjectForRefund($refundTransactionId);
        $this->helperMock->expects($this->once())
            ->method('clearTransactionId')
            ->with($refundTransactionId)
            ->willReturn($refundTransactionId);

        $transaction = \Braintree_Transaction::factory(
            [
                'id' => self::AUTH_TRAN_ID,
                'status' => \Braintree_Transaction::SETTLED,
                'amount' => $amount,
            ]
        );

        $this->braintreeTransactionMock->expects($this->once())
            ->method('find')
            ->with($refundTransactionId)
            ->willReturn($transaction);

        $resultError = $this->getMockBuilder('\Braintree_Result_Error')
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeTransactionMock->expects($this->once())
            ->method('refund')
            ->with($refundTransactionId)
            ->willReturn($resultError);
        $this->braintreeTransactionMock->expects($this->never())
            ->method('void');

        $this->errorHelperMock->expects($this->once())
            ->method('parseBraintreeError')
            ->with($resultError)
            ->willReturn(new \Magento\Framework\Phrase('error'));
        $this->model->refund($paymentObject, $amount);
    }
    //End: test refund

    //Start: test void
    protected function setupTransactionIds($orderId, $transactionIds)
    {
        $transactionCollectionMock = $this->getMockBuilder(
            '\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection'
        )->disableOriginalConstructor()
            ->getMock();
        $transactionCollectionMock->expects($this->once())
            ->method('addFieldToSelect')
            ->with('txn_id')
            ->willReturnSelf();
        $transactionCollectionMock->expects($this->once())
            ->method('addOrderIdFilter')
            ->with($orderId)
            ->willReturnSelf();
        $transactionCollectionMock->expects($this->once())
            ->method('addTxnTypeFilter')
            ->with([PaymentTransaction::TYPE_AUTH, PaymentTransaction::TYPE_CAPTURE])
            ->willReturnSelf();
        $transactionCollectionMock->expects($this->once())
            ->method('getColumnValues')
            ->with('txn_id')
            ->willReturn($transactionIds);

        $this->salesTransactionCollectionFactoryMock->expects($this->at(0))
            ->method('create')
            ->willReturn($transactionCollectionMock);

        $this->helperMock->expects($this->any())
            ->method('clearTransactionId')
            ->willReturnArgument(0);
    }

    protected function setupPaymentObjectForVoid($orderId)
    {
        $paymentObject = $this->objectManagerHelper->getObject(
            'Magento\Sales\Model\Order\Payment'
        );

        $orderMock = $this->objectManagerHelper->getObject(
            '\Magento\Sales\Model\Order',
            [
                'data' => [
                    'id' => $orderId,
                ]
            ]
        );
        $paymentObject->setOrder($orderMock);

        return $paymentObject;
    }

    public function testVoid()
    {
        $orderId = 1005;

        $paymentObject = $this->setupPaymentObjectForVoid($orderId);

        $transactions = [
            '1' => \Braintree_Transaction::factory(
                [
                    'id' => '1',
                    'status' => \Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT,
                ]
            ),
            '2' => \Braintree_Transaction::factory(
                [
                    'id' => '2',
                    'status' => \Braintree_Transaction::AUTHORIZED,
                ]
            ),
        ];
        $this->setupTransactionIds($orderId, array_keys($transactions));

        $index = 0;
        foreach ($transactions as $id => $transaction) {
            $this->braintreeTransactionMock->expects($this->at($index))
                ->method('find')
                ->with($id)
                ->willReturn($transaction);
            $index++;
        }

        foreach (array_keys($transactions) as $id) {
            $resultSuccess = $this->setupSuccessResponse([]);
            $this->braintreeTransactionMock->expects($this->at($index))
                ->method('void')
                ->with($id)
                ->willReturn($resultSuccess);
            $index++;
        }

        $index = 1;
        foreach (array_keys($transactions) as $id) {
            $transactionCollectionMock = $this->getMockBuilder(
                '\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection'
            )->disableOriginalConstructor()
                ->getMock();
            $transactionCollectionMock->expects($this->at(0))
                ->method('addFieldToFilter')
                ->with('parent_txn_id', ['eq' => $id])
                ->willReturnSelf();
            $transactionCollectionMock->expects($this->at(1))
                ->method('addFieldToFilter')
                ->with('txn_type', PaymentTransaction::TYPE_VOID)
                ->willReturnSelf();
            $transactionCollectionMock->expects($this->once())
                ->method('getSize')
                ->willReturn(1);
            $this->salesTransactionCollectionFactoryMock->expects($this->at($index))
                ->method('create')
                ->willReturn($transactionCollectionMock);
            $index++;
        }

        $this->model->void($paymentObject);
        $this->assertEquals(1, $paymentObject->getIsTransactionClosed());
        $this->assertEquals('Voided capture.', $paymentObject->getMessage()->getText());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage There was an error voiding the transaction:  error.
     */
    public function testVoidError()
    {
        $orderId = 1005;

        $paymentObject = $this->setupPaymentObjectForVoid($orderId);

        $transactions = [
            '1' => \Braintree_Transaction::factory(
                [
                    'id' => '1',
                    'status' => \Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT,
                ]
            ),
        ];
        $this->setupTransactionIds($orderId, array_keys($transactions));

        $index = 0;
        foreach ($transactions as $id => $transaction) {
            $this->braintreeTransactionMock->expects($this->at($index))
                ->method('find')
                ->with($id)
                ->willReturn($transaction);
            $index++;
        }

        foreach (array_keys($transactions) as $id) {
            $resultError = $this->getMockBuilder('\Braintree_Result_Error')
                ->disableOriginalConstructor()
                ->getMock();
            $this->errorHelperMock->expects($this->once())
                ->method('parseBraintreeError')
                ->with($resultError)
                ->willReturn(new \Magento\Framework\Phrase('error'));
            $this->braintreeTransactionMock->expects($this->at($index))
                ->method('void')
                ->with($id)
                ->willReturn($resultError);
            $index++;
        }

        $this->model->void($paymentObject);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Some transactions are already settled or voided and cannot be voided.
     */
    public function testVoidInvalidState()
    {
        $orderId = 1005;

        $paymentObject = $this->setupPaymentObjectForVoid($orderId);

        $transactions = [
            '1' => \Braintree_Transaction::factory(
                [
                    'id' => '1',
                    'status' => \Braintree_Transaction::SETTLED,
                ]
            ),
        ];
        $this->setupTransactionIds($orderId, array_keys($transactions));

        $index = 0;
        foreach ($transactions as $id => $transaction) {
            $this->braintreeTransactionMock->expects($this->at($index))
                ->method('find')
                ->with($id)
                ->willReturn($transaction);
            $index++;
        }

        $this->model->void($paymentObject);
    }
    //End: test void

    public function testGetConfigData()
    {
        $field = 'configFieldName';
        $storeId = '2';
        $configValue = 'configValue';

        $this->configMock->expects($this->once())
            ->method('getConfigData')
            ->with($field, $storeId)
            ->willReturn($configValue);

        $this->assertEquals($configValue, $this->model->getConfigData($field, $storeId));
    }

    public function testCanVoid()
    {
        $this->assertEquals(true, $this->model->canVoid());
    }

    public function testCanVoidWithExistingOrderWithInvoice()
    {
        $orderMock = $this->getMockBuilder('\Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'hasInvoices'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $orderMock->expects($this->once())
            ->method('hasInvoices')
            ->willReturn(true);
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_order')
            ->willReturn($orderMock);
        $this->assertEquals(false, $this->model->canVoid());
    }

    /**
     * @return \Magento\Directory\Model\PriceCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPriceCurrencyMock()
    {
        $currencyMock = $this->getMockBuilder('\Magento\Directory\Model\PriceCurrency')
            ->disableOriginalConstructor()
            ->setMethods(['round'])
            ->getMock();
        $currencyMock->expects(static::any())
            ->method('round')
            ->willReturnMap([
                [self::TOTAL_AMOUNT, round(self::TOTAL_AMOUNT, 2)],
                [self::AUTH_AMOUNT, round(self::AUTH_AMOUNT, 2)]
            ]);
        return $currencyMock;
    }
}
