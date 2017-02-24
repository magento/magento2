<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Paypal\Model\Payflowpro
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Payflowpro;
use Magento\Store\Model\ScopeInterface;
use Magento\Payment\Model\InfoInterface;

/**
 * Class PayflowproTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PayflowproTest extends \PHPUnit_Framework_TestCase
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
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Paypal\Model\Payflow\Service\Gateway|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gatewayMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManager;

    protected function setUp()
    {
        $configFactoryMock = $this->getMock(
            \Magento\Payment\Model\Method\ConfigInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->configMock = $this->getMock(
            \Magento\Paypal\Model\PayflowConfig::class,
            [],
            [],
            '',
            false
        );
        $client = $this->getMock(
            \Magento\Framework\HTTP\ZendClient::class,
            [],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore']
        );
        $this->gatewayMock = $this->getMock(
            \Magento\Paypal\Model\Payflow\Service\Gateway::class,
            [],
            [],
            '',
            false
        );
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();

        $configFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->configMock);

        $client->expects($this->any())->method('create')->will($this->returnSelf());
        $client->expects($this->any())->method('setUri')->will($this->returnSelf());
        $client->expects($this->any())->method('setConfig')->will($this->returnSelf());
        $client->expects($this->any())->method('setMethod')->will($this->returnSelf());
        $client->expects($this->any())->method('setParameterPost')->will($this->returnSelf());
        $client->expects($this->any())->method('setHeaders')->will($this->returnSelf());
        $client->expects($this->any())->method('setUrlEncodeBody')->will($this->returnSelf());
        $client->expects($this->any())->method('request')->will($this->returnSelf());
        $client->expects($this->any())->method('getBody')->will($this->returnValue('RESULT name=value&name2=value2'));

        $clientFactory = $this->getMock(\Magento\Framework\HTTP\ZendClientFactory::class, ['create'], [], '', false);
        $clientFactory->expects($this->any())->method('create')->will($this->returnValue($client));

        $this->eventManager = $this->getMockForAbstractClass(ManagerInterface::class);

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
     * @param mixed $amountPaid
     * @param string $paymentType
     * @param bool $expected
     * @dataProvider canVoidDataProvider
     */
    public function testCanVoid($amountPaid, $paymentType, $expected)
    {
        $payment = $this->helper->getObject($paymentType);
        $payment->setAmountPaid($amountPaid);
        $this->payflowpro->setInfoInstance($payment);
        $this->assertEquals($expected, $this->payflowpro->canVoid());
    }

    /**
     * @return array
     */
    public function canVoidDataProvider()
    {
        return [
            [0, \Magento\Sales\Model\Order\Payment::class, true],
            [null, \Magento\Sales\Model\Order\Payment::class, true]
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
        $payment = $this->getMock(\Magento\Payment\Model\Info::class, ['setTransactionId', '__wakeup'], [], '', false);
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
        /** @var \Magento\Sales\Model\Order\Payment $paymentMock */
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
                $this->isInstanceOf(\Magento\Paypal\Model\PayflowConfig::class)
            )
            ->willReturn($response);
        return $response;
    }

    /**
     * Create mock object for payment model
     * @return \PHPUnit_Framework_MockObject_MockObject
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
     * @return \PHPUnit_Framework_MockObject_MockObject
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
        $config = $this->getMock(ConfigInterface::class);

        $this->gatewayMock->expects(static::once())
            ->method('postRequest')
            ->with($request, $config)
            ->willReturn($expectedResult);

        static::assertSame($expectedResult, $this->payflowpro->postRequest($request, $config));
    }

    public function testPostRequestException()
    {
        $this->setExpectedException(
            LocalizedException::class,
            __('Payment Gateway is unreachable at the moment. Please use another payment option.')
        );

        $request = new DataObject();

        /** @var ConfigInterface $config */
        $config = $this->getMock(ConfigInterface::class);

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
}
