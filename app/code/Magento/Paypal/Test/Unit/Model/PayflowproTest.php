<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Payment\Model\Method\ConfigInterfaceFactory;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Paypal\Model\PayflowConfig;
use Magento\Paypal\Model\Payflowpro;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\ScopeInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PayflowproTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Payflowpro
     */
    protected $payflowpro;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $helper;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Gateway|MockObject
     */
    protected $gatewayMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManager;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(PayflowConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->gatewayMock = $this->getMockBuilder(Gateway::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $configFactoryMock = $this->getMockBuilder(ConfigInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $configFactoryMock->method('create')
            ->willReturn($this->configMock);

        $client = $this->getMockBuilder(ZendClient::class)
            ->getMock();

        $clientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientFactory->method('create')->will($this->returnValue($client));

        $this->eventManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->payflowpro = $this->helper->getObject(
            \Magento\Paypal\Model\Payflowpro::class,
            [
                'eventDispatcher' => $this->eventManager,
                'configFactory' => $configFactoryMock,
                'httpClientFactory' => $clientFactory,
                'storeManager' => $this->storeManagerMock,
                'gateway' => $this->gatewayMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * @covers \Magento\Paypal\Model\Payflowpro::canVoid
     *
     * @param string $message
     * @param int|null $amountPaid
     * @param bool $expected
     * @dataProvider canVoidDataProvider
     */
    public function testCanVoid($message, $amountPaid, $expected)
    {
        /** @var Payment|MockObject $payment */
        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $payment->method('getAmountPaid')->willReturn($amountPaid);
        $this->payflowpro->setInfoInstance($payment);

        $this->assertEquals($expected, $this->payflowpro->canVoid(), $message);
    }

    /**
     * @return array
     */
    public function canVoidDataProvider()
    {
        return [
            ["Can void transaction if order's paid amount not set", null, true],
            ["Can void transaction if order's paid amount equals zero", 0, true],
            ["Can't void transaction if order's paid amount greater than zero", 10, false],
        ];
    }

    public function testCanCapturePartial()
    {
        $this->assertTrue($this->payflowpro->canCapturePartial());
    }

    public function testCanRefundPartialPerInvoice()
    {
        $this->assertTrue($this->payflowpro->canRefundPartialPerInvoice());
    }

    /**
     * test for _buildBasicRequest (BDCODE)
     */
    public function testFetchTransactionInfoForBN()
    {
        $response = $this->getGatewayResponseObject();

        $this->gatewayMock->expects($this->once())
            ->method('postRequest')
            ->willReturn($response);
        $this->initStoreMock();
        $this->configMock->expects($this->once())->method('getBuildNotationCode')
            ->will($this->returnValue('BNCODE'));
        $payment = $this->createPartialMock(\Magento\Payment\Model\Info::class, ['setTransactionId', '__wakeup']);
        $payment->expects($this->once())->method('setTransactionId')->will($this->returnSelf());
        $this->payflowpro->fetchTransactionInfo($payment, 'AD49G8N825');
    }

    /**
     * @param $response
     * @dataProvider setTransStatusDataProvider
     */
    public function testSetTransStatus($response, $paymentExpected)
    {
        $payment = $this->helper->getObject(\Magento\Payment\Model\Info::class);
        $this->payflowpro->setTransStatus($payment, $response);
        $this->assertEquals($paymentExpected->getData(), $payment->getData());
    }

    public function setTransStatusDataProvider()
    {
        return [
            [
                'response' => new \Magento\Framework\DataObject(
                    [
                        'pnref' => 'V19A3D27B61E',
                        'result_code' => Payflowpro::RESPONSE_CODE_APPROVED,
                    ]
                ),
                'paymentExpected' => new \Magento\Framework\DataObject(
                    [
                        'transaction_id' => 'V19A3D27B61E',
                        'is_transaction_closed' => 0,
                    ]
                ),
            ],
            [
                'response' => new \Magento\Framework\DataObject(
                    [
                        'pnref' => 'V19A3D27B61E',
                        'result_code' => Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER
                    ]
                ),
                'paymentExpected' => new \Magento\Framework\DataObject(
                    [
                        'transaction_id' => 'V19A3D27B61E',
                        'is_transaction_closed' => 0,
                        'is_transaction_pending' => true,
                        'is_fraud_detected' => true,
                    ]
                ),
            ],
        ];
    }

    /**
     * @param array $expectsMethods
     * @param bool $result
     *
     * @dataProvider dataProviderForTestIsActive
     */
    public function testIsActive(array $expectsMethods, $result)
    {
        $storeId = 15;

        $i = 0;
        foreach ($expectsMethods as $method => $isActive) {
            $this->scopeConfigMock->expects($this->at($i++))
                ->method('getValue')
                ->with(
                    "payment/{$method}/active",
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                )->willReturn($isActive);
        }

        $this->assertEquals($result, $this->payflowpro->isActive($storeId));
    }

    /**
     * @covers \Magento\Paypal\Model\Payflowpro::capture
     */
    public function testCaptureWithBuildPlaceRequest()
    {
        $paymentMock = $this->getPaymentMock();
        $orderMock = $this->getOrderMock();

        // test case to build basic request
        $paymentMock->expects(static::once())
            ->method('getAdditionalInformation')
            ->with('pnref')
            ->willReturn(false);
        $paymentMock->expects(static::once())
            ->method('getParentTransactionId')
            ->willReturn(false);

        $paymentMock->expects(static::exactly(2))
            ->method('getOrder')
            ->willReturn($orderMock);

        $response = $this->execGatewayRequest();
        $amount = 23.03;
        $this->payflowpro->capture($paymentMock, $amount);
        static::assertEquals($response['pnref'], $paymentMock->getTransactionId());
        static::assertFalse((bool)$paymentMock->getIsTransactionPending());
    }

    /**
     * @return array
     */
    public function dataProviderCaptureAmountRounding()
    {
        return [
            [
                'amount' => 14.13999999999999999999999999999999999999999999999999,
                'setAmount' => 49.99,
                'expectedResult' => 14.14
            ],
            [
                'amount' => 14.13199999999999999999999999999999999999999999999999,
                'setAmount' => 49.99,
                'expectedResult' => 14.13,
            ],
            [
                'amount' => 14.14,
                'setAmount' => 49.99,
                'expectedResult' => 14.14,
            ],
            [
                'amount' => 14.13999999999999999999999999999999999999999999999999,
                'setAmount' => 14.14,
                'expectedResult' => 0,
            ]
        ];
    }

    /**
     * @param float $amount
     * @param float $setAmount
     * @param float $expectedResult
     * @dataProvider dataProviderCaptureAmountRounding
     */
    public function testCaptureAmountRounding($amount, $setAmount, $expectedResult)
    {
        $paymentMock = $this->getPaymentMock();
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $infoInstanceMock = $this->getMockForAbstractClass(
            InfoInterface::class,
            [],
            '',
            false,
            false,
            false,
            ['getAmountAuthorized','hasAmountPaid']
        );

        $infoInstanceMock->expects($this->once())
            ->method('getAmountAuthorized')
            ->willReturn($setAmount);
        $infoInstanceMock->expects($this->once())
            ->method('hasAmountPaid')
            ->willReturn(true);
        $this->payflowpro->setData('info_instance', $infoInstanceMock);

        // test case to build basic request
        $paymentMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('pnref')
            ->willReturn(false);
        $paymentMock->expects($this->any())
            ->method('getParentTransactionId')
            ->willReturn(true);

        $paymentMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->initStoreMock();
        $response = $this->getGatewayResponseObject();
        $this->gatewayMock->expects($this->once())
            ->method('postRequest')
            ->with(
                $this->callback(function($request) use ($expectedResult)
                {
                    return is_callable([$request, 'getAmt']) && $request->getAmt() == $expectedResult;
                }
            ),
                $this->isInstanceOf(PayflowConfig::class)
            )
            ->willReturn($response);

        $this->payflowpro->capture($paymentMock, $amount);

        $this->assertEquals($response['pnref'], $paymentMock->getTransactionId());
        $this->assertFalse((bool)$paymentMock->getIsTransactionPending());
    }

    /**
     * @covers \Magento\Paypal\Model\Payflowpro::authorize
     */
    public function testAuthorize()
    {
        $paymentMock = $this->getPaymentMock();
        $orderMock = $this->getOrderMock();

        $paymentMock->expects(static::exactly(2))
            ->method('getOrder')
            ->willReturn($orderMock);

        $response = $this->execGatewayRequest();
        $amount = 43.20;
        $this->payflowpro->authorize($paymentMock, $amount);
        static::assertEquals($response['pnref'], $paymentMock->getTransactionId());
        static::assertFalse((bool)$paymentMock->getIsTransactionPending());
    }

    /**
     * @return array
     */
    public function dataProviderForTestIsActive()
    {
        return [
            [
                'expectsMethods' => [
                    Config::METHOD_PAYFLOWPRO => 0,
                    Config::METHOD_PAYMENT_PRO => 1,
                ],
                'result' => true,
            ],
            [
                'expectsMethods' => [
                    Config::METHOD_PAYFLOWPRO => 1
                ],
                'result' => true,
            ],
            [
                'expectsMethods' => [
                    Config::METHOD_PAYFLOWPRO => 0,
                    Config::METHOD_PAYMENT_PRO => 0,
                ],
                'result' => false,
            ],
        ];
    }

    /**
     * @covers \Magento\Paypal\Model\Payflowpro::refund()
     */
    public function testRefund()
    {
        /** @var Payment $paymentMock */
        $paymentMock = $this->getPaymentMock();

        $response = $this->execGatewayRequest();

        $amount = 213.04;
        $this->payflowpro->refund($paymentMock, $amount);
        static::assertEquals($response['pnref'], $paymentMock->getTransactionId());
        static::assertTrue($paymentMock->getIsTransactionClosed());
    }

    /**
     * Create mock object for store model
     * @return void
     */
    protected function initStoreMock()
    {
        $storeId = 27;
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $this->storeManagerMock->expects(static::once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);
    }

    /**
     * Create response object for Payflowpro gateway
     * @return \Magento\Framework\DataObject
     */
    protected function getGatewayResponseObject()
    {
        return new \Magento\Framework\DataObject(
            [
                'result' => '0',
                'pnref' => 'V19A3D27B61E',
                'respmsg' => 'Approved',
                'authcode' => '510PNI',
                'hostcode' => 'A',
                'request_id' => 'f930d3dc6824c1f7230c5529dc37ae5e',
                'result_code' => '0',
            ]
        );
    }

    /**
     * Call payflow gateway request and return response object
     * @return \Magento\Framework\DataObject
     */
    protected function execGatewayRequest()
    {
        $this->initStoreMock();
        $response = $this->getGatewayResponseObject();
        $this->gatewayMock->expects(static::once())
            ->method('postRequest')
            ->with(
                $this->isInstanceOf(\Magento\Framework\DataObject::class),
                $this->isInstanceOf(PayflowConfig::class)
            )
            ->willReturn($response);
        return $response;
    }

    /**
     * Create mock object for payment model
     * @return MockObject
     */
    protected function getPaymentMock()
    {
        $paymentMock = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getAdditionalInformation', 'getParentTransactionId', 'getOrder',
                'getCcNumber', 'getCcExpMonth', 'getCcExpYear', 'getCcCid'
            ])
            ->getMock();

        $cardData = [
            'number' => 4111111111111111,
            'month' => 12,
            'year' => 18,
            'cvv' => 123
        ];
        $paymentMock->expects(static::any())
            ->method('getCcNumber')
            ->willReturn($cardData['number']);
        $paymentMock->expects(static::any())
            ->method('getCcExpMonth')
            ->willReturn($cardData['month']);
        $paymentMock->expects(static::any())
            ->method('getCcExpYear')
            ->willReturn($cardData['year']);
        $paymentMock->expects(static::any())
            ->method('getCcCid')
            ->willReturn($cardData['cvv']);
        return $paymentMock;
    }

    /**
     * Create mock object for order model
     * @return MockObject
     */
    protected function getOrderMock()
    {
        $orderData = [
            'currency' => 'USD',
            'id' => 4,
            'increment_id' => '0000004'
        ];
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrencyCode', 'getIncrementId', 'getId', 'getBillingAddress', 'getShippingAddress'])
            ->getMock();

        $orderMock->expects(static::once())
            ->method('getId')
            ->willReturn($orderData['id']);
        $orderMock->expects(static::once())
            ->method('getBaseCurrencyCode')
            ->willReturn($orderData['currency']);
        $orderMock->expects(static::atLeastOnce())
            ->method('getIncrementId')
            ->willReturn($orderData['increment_id']);
        return $orderMock;
    }

    public function testPostRequest()
    {
        $expectedResult = new DataObject();

        $request = new DataObject();

        /** @var ConfigInterface $config */
        $config = $this->createMock(ConfigInterface::class);

        $this->gatewayMock->expects(static::once())
            ->method('postRequest')
            ->with($request, $config)
            ->willReturn($expectedResult);

        static::assertSame($expectedResult, $this->payflowpro->postRequest($request, $config));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Payment Gateway is unreachable at the moment. Please use another payment option.
     */
    public function testPostRequestException()
    {
        $request = new DataObject();

        /** @var ConfigInterface $config */
        $config = $this->createMock(ConfigInterface::class);

        $this->gatewayMock->expects(static::once())
            ->method('postRequest')
            ->with($request, $config)
            ->willThrowException(new \Zend_Http_Client_Exception());

        $this->payflowpro->postRequest($request, $config);
    }

    /**
     * @covers \Magento\Paypal\Model\Payflowpro::assignData
     */
    public function testAssignData()
    {
        $data = [
            'cc_type' => 'VI',
            'cc_last_4' => 1111,
            'cc_exp_month' => 12,
            'cc_exp_year' => 2023
        ];
        $dataObject = new DataObject($data);

        $infoInstance = $this->getMockForAbstractClass(InfoInterface::class);
        $this->payflowpro->setData('info_instance', $infoInstance);

        $this->eventManager->expects(static::exactly(2))
            ->method('dispatch');

        $this->payflowpro->assignData($dataObject);
    }

    /**
     * Asserts that PayPal gateway response mapping correctly.
     *
     * @param array $postData
     * @param DataObject $expectedResponse
     * @dataProvider dataProviderMapGatewayResponse
     */
    public function testMapGatewayResponse($postData, $expectedResponse)
    {
        self::assertEquals(
            $this->payflowpro->mapGatewayResponse($postData, new DataObject()),
            $expectedResponse
        );
    }

    /**
     * @return array
     */
    public function dataProviderMapGatewayResponse()
    {
        return [
            [
                [
                    'BILLTONAME' => 'John Doe',
                    'BILLTOFIRSTNAME' => 'John',
                    'BILLTOLASTNAME' => 'Doe',
                    'BILLTOEMAIL' => 'user@magento.com',
                    'BILLTOSTREET' => '6161 West Centinela Avenue',
                    'BILLTOCITY' => 'Culver City',
                    'BILLTOSTATE' => 'CA',
                    'BILLTOZIP' => '90230',
                    'BILLTOCOUNTRY' => 'US',
                    'SHIPTOSTREET' => '6161 West Centinela Avenue',
                    'SHIPTOCITY' => 'Culver City',
                    'SHIPTOSTATE' => 'CA',
                    'SHIPTOZIP' => '90230',
                    'SHIPTOCOUNTRY' => 'US',
                    'NAMETOSHIP' => 'John Doe',
                    'ADDRESSTOSHIP' => '6161 West Centinela Avenue',
                    'CITYTOSHIP' => 'Culver City',
                    'STATETOSHIP' => 'CA',
                    'ZIPTOSHIP' => '90230',
                    'COUNTRYTOSHIP' => 'US',
                    'NAME' => 'John Doe',
                    'CVV2MATCH' => 'Y',
                    'CARDTYPE' => '0',
                    'AVSDATA' => 'NNN',
                    'AVSZIP' => 'N',
                    'AVSADDR' => 'N',
                ],
                new DataObject([
                    'billtoname' => 'John Doe',
                    'billtofirstname' => 'John',
                    'billtolastname' => 'Doe',
                    'billtoemail' => 'user@magento.com',
                    'billtostreet' => '6161 West Centinela Avenue',
                    'billtocity' => 'Culver City',
                    'billtostate' => 'CA',
                    'billtozip' => '90230',
                    'billtocountry' => 'US',
                    'shiptostreet' => '6161 West Centinela Avenue',
                    'shiptocity' => 'Culver City',
                    'shiptostate' => 'CA',
                    'shiptozip' => '90230',
                    'shiptocountry' => 'US',
                    'nametoship' => 'John Doe',
                    'addresstoship' => '6161 West Centinela Avenue',
                    'citytoship' => 'Culver City',
                    'statetoship' => 'CA',
                    'ziptoship' => '90230',
                    'countrytoship' => 'US',
                    'name' => 'John Doe',
                    'cvv2match' => 'Y',
                    'cardtype' => '0',
                    'avsdata' => 'NN',
                    'avszip' => 'N',
                    'avsaddr' => 'N',
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'address' => '6161 West Centinela Avenue',
                    'city' => 'Culver City',
                    'state' => 'CA',
                    'zip' => '90230',
                    'country' => 'US',
                    'email' => 'user@magento.com',
                    'cscmatch' => 'Y',
                    'ccavsstatus' => 'NNN',
                    'cc_type' => 'VI',
                ]),
            ]
        ];
    }
}
