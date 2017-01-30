<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model\PaymentMethod;

use Magento\Braintree\Model\PaymentMethod;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory as TransactionCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use \Braintree_Result_Successful;
use \Braintree_Result_Error;
use \Braintree_Transaction;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;

/**
 * Class PayPalTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PayPalTest extends \PHPUnit_Framework_TestCase
{
    const CHANNEL = 'Magento Community Edition 2.0';
    const PAYMENT_METHOD_NONCE = 'nonce';
    const CC_TOKEN = 'cc45kn';
    const MERCHANT_ACCOUNT_ID = '5z4rh64p772cy7pb';
    const FNAME = 'John';
    const LNAME = 'Doe';
    const AUTH_TRAN_ID = 'r4z34j';
    const AUTH_AMOUNT = 5.76;
    const AUTH_CC_LAST_4 = '0004';
    const CUSTOMER_ID = '221b3649effb4bb1b62fc940691bd18c';
    const PAYER_EMAIL = 'jogndoe@example.com';
    const PAYER_ID = 'PAYERID';
    const AUTHORIZATION_ID = 'PAY-ID';
    const PAYMENT_ID = 'PAYMENT-ID';

    /**
     * @var \Magento\Braintree\Model\PaymentMethod\PayPal
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
     * @var \Magento\Payment\Model\InfoInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Braintree\Model\Config\PayPal|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payPalConfigMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('\Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->getMock();
        $this->payPalConfigMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\PayPal')
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

        $this->psrLoggerMock = $this->getMock('\\Psr\Log\LoggerInterface');
        $this->contextMock->expects($this->any())
            ->method('getLogger')
            ->willReturn($this->psrLoggerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Model\PaymentMethod\PayPal',
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
                'payPalConfig' => $this->payPalConfigMock,
            ]
        );

        $this->infoInstanceMock = $this->getMockForAbstractClass(
            '\Magento\Payment\Model\InfoInterface',
            [],
            '',
            false,
            false,
            false,
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
                'getCcType',
            ]
        );

        $this->productMetaDataMock->expects($this->any())
            ->method('getEdition')
            ->willReturn('Community Edition');
        $this->productMetaDataMock->expects($this->any())
            ->method('getVersion')
            ->willReturn('2.0');
    }

    public function testAssignData()
    {
        $ccLast4 = '9216';
        $ccToken = 'erf6re';
        $paymentMethodNonce = 'nonce';
        $storeInVault = true;
        $data = [
            'additional_data' => [
                'cc_last4' => $ccLast4,
                'cc_token' => $ccToken,
                'payment_method_nonce' => $paymentMethodNonce,
                'store_in_vault' => $storeInVault
            ]
        ];
        $data = new \Magento\Framework\DataObject($data);
        $this->model->setInfoInstance($this->infoInstanceMock);

        //ignore all fields except for payment_method_nonce
        $this->infoInstanceMock->expects($this->once())
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
    protected function setupInfoInstance(
        $countryId,
        $ccType,
        $ccToken = null
    ) {
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

        $this->payPalConfigMock->expects($this->once())
            ->method('canUseForCountry')
            ->with($countryId)
            ->willReturn(false);
        $this->model->validate();
    }

    public function testValidate()
    {
        $countryId = 'US';
        $this->setupInfoInstance($countryId, null);

        $this->model->setInfoInstance($this->infoInstanceMock);

        $this->payPalConfigMock->expects($this->once())
            ->method('canUseForCountry')
            ->with($countryId)
            ->willReturn(true);
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
        return $orderMock;
    }

    /**
     * @param \Magento\Framework\DataObject $paymentObject
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setupPaymentObject(
        \Magento\Framework\DataObject $paymentObject,
        $storeId
    ) {
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

        $billingAddress = new \Magento\Framework\DataObject(
            $addressData
        );
        $addressData['address_type'] = 'shipping';
        $shippingAddress = new \Magento\Framework\DataObject(
            $addressData
        );

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
            'firstName'         => self::FNAME,
            'lastName'          => self::LNAME,
            'company'           => $company,
            'streetAddress'     => $street,
            'extendedAddress'   => $street2,
            'locality'          => $city,
            'region'            => $regionCode,
            'postalCode'        => $postcode,
            'countryCodeAlpha2' => $countryId,
        ];
        return [
            'channel'   => self::CHANNEL,
            'orderId'   => $orderId,
            'customer'  => [
                'firstName' => self::FNAME,
                'lastName'  => self::LNAME,
                'company'   => $company,
                'phone'     => $phone,
                'fax'       => $fax,
                'email'     => $customerEmail,
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
     * @param array $paymentInfo
     * @param array $expectedRequestAttributes
     * @param array $braintreeResponse
     * @param array $expectedPaymentFields
     * @dataProvider authorizeDataProvider
     */
    public function testAuthorizeSuccess(
        array $configData,
        array $paymentInfo,
        array $expectedRequestAttributes,
        array $braintreeResponse,
        array $expectedPaymentFields
    ) {
        $storeId = 3;
        $amount = self::AUTH_AMOUNT;
        $paymentObject = $this->objectManagerHelper->getObject('Magento\Sales\Model\Order\Payment');


        $expectedRequestAttribs = $this->setupAuthorizeRequest(
            $configData,
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
            'paypal_payment_nonce' => [
                'config' => [
                    'is3dSecureEnabled' => false,
                    'isFraudProtectionEnabled' => false,
                    'isDebugEnabled' => false,
                    'useVault' => false,
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'infoInstanceValueMap' => [
                    ['store_in_vault', null],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'options' => [
                        'addBillingAddressToPaymentMethod' => true,
                    ],
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodNonce' => self::PAYMENT_METHOD_NONCE,
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                            'paypal' => [
                                'payerEmail' => self::PAYER_EMAIL,
                                'paymentId' => self::PAYMENT_ID,
                                'authorizationId' => self::AUTHORIZATION_ID,
                                'payerId' => self::PAYER_ID,
                                'payerFirstName' => self::FNAME,
                                'payerLastName' => self::LNAME,
                            ]
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                        'payerEmail' => self::PAYER_EMAIL,
                        'paymentId' => self::PAYMENT_ID,
                        'authorizationId' => self::AUTHORIZATION_ID,
                        'payerId' => self::PAYER_ID,
                        'payerFirstName' => self::FNAME,
                        'payerLastName' => self::LNAME,
                    ],
                ],
            ],
            'paypal_payment_nonce_fraud_protection' => [
                'config' => [
                    'is3dSecureEnabled' => false,
                    'isFraudProtectionEnabled' => true,
                    'isDebugEnabled' => false,
                    'useVault' => false,
                    'getMerchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                ],
                'infoInstanceValueMap' => [
                    ['store_in_vault', null],
                    ['payment_method_nonce', self::PAYMENT_METHOD_NONCE],
                    ['device_data', 'fraud_detection_data'],
                ],
                'expectedParams' => [
                    'options' => [
                        'addBillingAddressToPaymentMethod' => true,
                    ],
                    'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                    'paymentMethodNonce' => self::PAYMENT_METHOD_NONCE,
                    'deviceData' => 'fraud_detection_data',
                ],
                'braintree_response' => [
                    'transaction' => \Braintree_Transaction::factory(
                        [
                            'id' => self::AUTH_TRAN_ID,
                            'gatewayRejectionReason' => null,
                            'processorAuthorizationCode' => 'S02T5Q',
                            'processorResponseCode' => '1000',
                            'processorResponseText' => 'Approved',
                            'paypal' => [
                                'payerEmail' => self::PAYER_EMAIL,
                                'paymentId' => self::PAYMENT_ID,
                                'authorizationId' => self::AUTHORIZATION_ID,
                                'payerId' => self::PAYER_ID,
                                'payerFirstName' => self::FNAME,
                                'payerLastName' => self::LNAME,
                            ]
                        ]
                    ),
                ],
                'expected_payment_fields' => [
                    'status' => 'APPROVED',
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                    'last_trans_id' => self::AUTH_TRAN_ID,
                    'transaction_id' => self::AUTH_TRAN_ID,
                    'is_transactioN_cloned' => 0,
                    'amount' => self::AUTH_AMOUNT,
                    'should_close_parent_transaction' => false,
                    'additional_information' => [
                        'processorAuthorizationCode' => 'S02T5Q',
                        'processorResponseCode' => '1000',
                        'processorResponseText' => 'Approved',
                        'payerEmail' => self::PAYER_EMAIL,
                        'paymentId' => self::PAYMENT_ID,
                        'authorizationId' => self::AUTHORIZATION_ID,
                        'payerId' => self::PAYER_ID,
                        'payerFirstName' => self::FNAME,
                        'payerLastName' => self::LNAME,
                    ],
                ],
            ],
        ];
    }
    //End: test authorize

    //Start: test capture
    protected function setupPaymentObjectForCapture(
        $paymentId
    ) {
        $paymentObject = $this->objectManagerHelper->getObject(
            'Magento\Sales\Model\Order\Payment',
            [
                'data' => [
                    'id' => $paymentId,
                    'cc_trans_id' => self::AUTH_TRAN_ID,
                ]
            ]
        );

        return $paymentObject;
    }
    public function testCaptureSuccess()
    {
        $amount = self::AUTH_AMOUNT;
        $paymentId = 1005;

        $paymentObject = $this->setupPaymentObjectForCapture($paymentId);

        $successResult = $this->setupSuccessResponse([]);
        $this->braintreeTransactionMock->expects($this->once())
            ->method('submitForSettlement')
            ->with(self::AUTH_TRAN_ID, $amount)
            ->willReturn($successResult);

        $this->psrLoggerMock->expects($this->never())
            ->method('critical');

        $this->model->capture($paymentObject, $amount);
        $this->assertEquals(false, $paymentObject->getIsTransactionClosed());
        $this->assertEquals(true, $paymentObject->getShouldCloseParentTransaction());
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
    //End: test capture

    public function testGetConfigData()
    {
        $field = 'configFieldName';
        $storeId = '2';
        $configValue = 'configValue';

        $this->payPalConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with($field, $storeId)
            ->willReturn($configValue);

        $this->assertEquals($configValue, $this->model->getConfigData($field, $storeId));
    }
}
