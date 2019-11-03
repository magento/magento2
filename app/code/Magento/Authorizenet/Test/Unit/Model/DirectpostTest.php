<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Test\Unit\Model;

use Magento\Authorizenet\Helper\Backend\Data;
use Magento\Authorizenet\Helper\Data as HelperData;
use Magento\Authorizenet\Model\Directpost\Response;
use Magento\Authorizenet\Model\Directpost\Response\Factory as ResponseFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\Framework\Simplexml\Element;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Authorizenet\Model\Directpost;
use Magento\Authorizenet\Model\TransactionService;
use Magento\Authorizenet\Model\Request;
use Magento\Authorizenet\Model\Directpost\Request\Factory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use PHPUnit\Framework\MockObject_MockBuilder;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionClass;

/**
 * Class DirectpostTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DirectpostTest extends TestCase
{
    const TOTAL_AMOUNT = 100.02;
    const INVOICE_NUM = '00000001';
    const TRANSACTION_ID = '41a23x34fd124';

    /**
     * @var Directpost
     */
    protected $directpost;

    /**
     * @var ScopeConfigInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var InfoInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var HelperData|PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var ResponseFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseFactoryMock;

    /**
     * @var TransactionRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionRepositoryMock;

    /**
     * @var Response|PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var TransactionService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionServiceMock;

    /**
     * @var ZendClient|PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpClientMock;

    /**
     * @var Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestFactory;

    /**
     * @var PaymentFailuresInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentFailures;

    /**
     * @var ZendClientFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->initPaymentMock();
        $this->initResponseFactoryMock();
        $this->initHttpClientMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
        $this->dataHelperMock = $this->getMockBuilder(HelperData::class)->disableOriginalConstructor()->getMock();
        $this->transactionRepositoryMock = $this->getMockBuilder(TransactionRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByTransactionId'])
            ->getMock();
        $this->transactionServiceMock = $this->getMockBuilder(TransactionService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTransactionDetails'])
            ->getMock();
        $this->paymentFailures = $this->getMockBuilder(PaymentFailuresInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->httpClientFactoryMock = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $helper = new ObjectManagerHelper($this);
        $this->directpost = $helper->getObject(
            Directpost::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'dataHelper' => $this->dataHelperMock,
                'requestFactory' => $this->requestFactory,
                'responseFactory' => $this->responseFactoryMock,
                'transactionRepository' => $this->transactionRepositoryMock,
                'transactionService' => $this->transactionServiceMock,
                'httpClientFactory' => $this->httpClientFactoryMock,
                'paymentFailures' => $this->paymentFailures,
            ]
        );
    }

    /**
     * Create mock for response factory
     *
     * @return void
     */
    private function initResponseFactoryMock()
    {
        $this->responseFactoryMock = $this->getMockBuilder(ResponseFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(Response::class)
            ->setMethods(
                [
                    'isValidHash',
                    'getXTransId',
                    'getXResponseCode',
                    'getXResponseReasonCode',
                    'getXResponseReasonText',
                    'getXAmount',
                    'setXResponseCode',
                    'setXResponseReasonCode',
                    'setXAvsCode',
                    'setXResponseReasonText',
                    'setXTransId',
                    'setXInvoiceNum',
                    'setXAmount',
                    'setXMethod',
                    'setXType',
                    'setData',
                    'getData',
                    'setXAccountNumber',
                    '__wakeup'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseFactoryMock->expects($this->any())->method('create')->willReturn($this->responseMock);
    }

    /**
     * Create mock for payment
     *
     * @return void
     */
    private function initPaymentMock()
    {
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getOrder',
                    'setAmount',
                    'setAnetTransType',
                    'setXTransId',
                    'getId',
                    'setAdditionalInformation',
                    'getAdditionalInformation',
                    'setIsTransactionDenied',
                    'setIsTransactionClosed',
                    'decrypt',
                    'getCcLast4',
                    'getParentTransactionId',
                    'getPoNumber'
                ]
            )
            ->getMock();
    }

    /**
     * Create a mock for http client
     *
     * @return void
     */
    private function initHttpClientMock()
    {
        $this->httpClientMock = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['request', 'getBody', '__wakeup'])
            ->getMock();
    }

    public function testGetConfigInterface()
    {
        $this->assertInstanceOf(ConfigInterface::class, $this->directpost->getConfigInterface());
    }

    public function testGetConfigValue()
    {
        $field = 'some_field';
        $returnValue = 'expected';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('payment/authorizenet_directpost/' . $field)
            ->willReturn($returnValue);
        $this->assertEquals($returnValue, $this->directpost->getValue($field));
    }

    public function testSetDataHelper()
    {
        $storeId = 'store-id';
        $expectedResult = 'relay-url';

        $helperDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helperDataMock->expects($this->once())
            ->method('getRelayUrl')
            ->with($storeId)
            ->willReturn($expectedResult);

        $this->directpost->setDataHelper($helperDataMock);
        $this->assertEquals($expectedResult, $this->directpost->getRelayUrl($storeId));
    }

    public function testAuthorize()
    {
        $paymentAction = 'some_action';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('payment/authorizenet_directpost/payment_action', 'store', null)
            ->willReturn($paymentAction);
        $this->paymentMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('payment_type', $paymentAction);

        $this->directpost->authorize($this->paymentMock, 10);
    }

    /**
     * @dataProvider dataProviderCaptureWithInvalidAmount
     * @expectedExceptionMessage Invalid amount for capture.
     * @expectedException \Magento\Framework\Exception\LocalizedException
     *
     * @param int $invalidAmount
     */
    public function testCaptureWithInvalidAmount($invalidAmount)
    {
        $this->directpost->capture($this->paymentMock, $invalidAmount);
    }

    /**
     * @return array
     */
    public function dataProviderCaptureWithInvalidAmount()
    {
        return [
            [0],
            [0.000],
            [-1.000],
            [-1],
            [null],
        ];
    }

    /**
     * Test capture has parent transaction id.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCaptureHasParentTransactionId()
    {
        $amount = 10;

        $this->paymentMock->expects($this->once())->method('setAmount')->with($amount);
        $this->paymentMock->expects($this->exactly(2))->method('getParentTransactionId')->willReturn(1);
        $this->paymentMock->expects($this->once())->method('setAnetTransType')->willReturn('PRIOR_AUTH_CAPTURE');

        $this->paymentMock->expects($this->once())->method('getId')->willReturn(1);
        $orderMock = $this->getOrderMock();
        $orderMock->expects($this->once())->method('getId')->willReturn(1);
        $this->paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);

        $transactionMock = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $this->transactionRepositoryMock->expects($this->once())
            ->method('getByTransactionId')
            ->with(1, 1, 1)
            ->willReturn($transactionMock);

        $this->paymentMock->expects($this->once())->method('setXTransId');
        $this->responseMock->expects($this->once())->method('getData')->willReturn([1]);

        $this->directpost->capture($this->paymentMock, 10);
    }

    /**
     * @@expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCaptureWithoutParentTransactionId()
    {
        $amount = 10;

        $this->paymentMock->expects($this->once())->method('setAmount')->with($amount);
        $this->paymentMock->expects($this->once())->method('getParentTransactionId')->willReturn(null);
        $this->responseMock->expects($this->once())->method('getData')->willReturn([1]);

        $this->directpost->capture($this->paymentMock, 10);
    }

    public function testCaptureWithoutParentTransactionIdWithoutData()
    {
        $amount = 10;

        $this->paymentMock->expects($this->once())->method('setAmount')->with($amount);
        $this->paymentMock->expects($this->exactly(2))->method('getParentTransactionId')->willReturn(null);
        $this->responseMock->expects($this->once())->method('getData')->willReturn([]);

        $this->paymentMock->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(0)
            ->willReturnSelf();

        $this->httpClientFactoryMock->expects($this->once())->method('create')->willReturn($this->httpClientMock);
        $this->httpClientMock->expects($this->once())->method('request')->willReturnSelf();

        $this->buildRequestTest();
        $this->postRequestTest();

        $this->directpost->capture($this->paymentMock, 10);
    }

    private function buildRequestTest()
    {
        $orderMock = $this->getOrderMock();
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $orderMock->expects($this->exactly(2))->method('getIncrementId')->willReturn(self::INVOICE_NUM);
        $this->paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);

        $this->addRequestMockToRequestFactoryMock();
    }

    private function postRequestTest()
    {
        $this->httpClientFactoryMock->expects($this->once())->method('create')->willReturn($this->httpClientMock);
        $this->httpClientMock->expects($this->once())->method('request')->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setXResponseCode')->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setXResponseReasonCode')->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setXResponseReasonText')->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setXAvsCode')->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setXTransId')->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setXInvoiceNum')->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setXAmount')->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setXMethod')->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setXType')->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setData')->willReturnSelf();

        $response = $this->getRefundResponseBody(
            Directpost::RESPONSE_CODE_APPROVED,
            Directpost::RESPONSE_REASON_CODE_APPROVED,
            'Successful'
        );
        $this->httpClientMock->expects($this->once())->method('getBody')->willReturn($response);
        $this->responseMock->expects($this->once())
            ->method('getXResponseCode')
            ->willReturn(Directpost::RESPONSE_CODE_APPROVED);
        $this->responseMock->expects($this->once())
            ->method('getXResponseReasonCode')
            ->willReturn(Directpost::RESPONSE_REASON_CODE_APPROVED);
        $this->dataHelperMock->expects($this->never())->method('wrapGatewayError');
    }

    public function testGetCgiUrl()
    {
        $url = 'cgi/url';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('payment/authorizenet_directpost/cgi_url', 'store', null)
            ->willReturn($url);

        $this->assertEquals($url, $this->directpost->getCgiUrl());
    }

    public function testGetCgiUrlWithEmptyConfigValue()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('payment/authorizenet_directpost/cgi_url', 'store', null)
            ->willReturn(null);

        $this->assertEquals(Directpost::CGI_URL, $this->directpost->getCgiUrl());
    }

    public function testGetRelayUrl()
    {
        $storeId = 100;
        $url = 'relay/url';
        $this->directpost->setData('store', $storeId);

        $this->dataHelperMock->expects($this->exactly(2))
            ->method('getRelayUrl')
            ->with($storeId)
            ->willReturn($url);

        $this->assertEquals($url, $this->directpost->getRelayUrl());
        $this->assertEquals($url, $this->directpost->getRelayUrl($storeId));
    }

    public function testGetResponse()
    {
        $this->assertSame($this->responseMock, $this->directpost->getResponse());
    }

    public function testSetResponseData()
    {
        $data = [
            'key' => 'value'
        ];

        $this->responseMock->expects($this->once())
            ->method('setData')
            ->with($data)
            ->willReturnSelf();

        $this->assertSame($this->directpost, $this->directpost->setResponseData($data));
    }

    public function testValidateResponseSuccess()
    {
        $this->prepareTestValidateResponse('some_md5', 'login', true);
        $this->assertEquals(true, $this->directpost->validateResponse());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testValidateResponseFailure()
    {
        $this->prepareTestValidateResponse('some_md5', 'login', false);
        $this->directpost->validateResponse();
    }

    /**
     * @param string $transMd5
     * @param string $login
     * @param bool $isValidHash
     */
    protected function prepareTestValidateResponse($transMd5, $login, $isValidHash)
    {
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap(
                [
                    ['payment/authorizenet_directpost/trans_md5', 'store', null, $transMd5],
                    ['payment/authorizenet_directpost/login', 'store', null, $login]
                ]
            );
        $this->responseMock->expects($this->exactly(1))
            ->method('isValidHash')
            ->with($transMd5, $login)
            ->willReturn($isValidHash);
    }

    public function testCheckTransIdSuccess()
    {
        $this->responseMock->expects($this->once())
            ->method('getXTransId')
            ->willReturn('111');

        $this->assertEquals(true, $this->directpost->checkTransId());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCheckTransIdFailure()
    {
        $this->responseMock->expects($this->once())
            ->method('getXTransId')
            ->willReturn(null);

        $this->directpost->checkTransId();
    }

    /**
     * @param bool $responseCode
     *
     * @dataProvider checkResponseCodeSuccessDataProvider
     */
    public function testCheckResponseCodeSuccess($responseCode)
    {
        $this->responseMock->expects($this->once())
            ->method('getXResponseCode')
            ->willReturn($responseCode);

        $this->assertEquals(true, $this->directpost->checkResponseCode());
    }

    /**
     * @return array
     */
    public function checkResponseCodeSuccessDataProvider()
    {
        return [
            ['responseCode' => Directpost::RESPONSE_CODE_APPROVED],
            ['responseCode' => Directpost::RESPONSE_CODE_HELD]
        ];
    }

    /**
     * Checks response failures behaviour.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCheckResponseCodeFailureDefault()
    {
        $responseCode = 999999;
        $this->responseMock->expects($this->once())->method('getXResponseCode')->willReturn($responseCode);

        $this->directpost->checkResponseCode();
    }

    /**
     * Checks response failures behaviour.
     *
     * @param int $responseCode
     * @param int $failuresHandlerCalls
     * @return void
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider checkResponseCodeFailureDataProvider
     */
    public function testCheckResponseCodeFailureDeclinedOrError(int $responseCode, int $failuresHandlerCalls): void
    {
        $reasonText = 'reason text';

        $this->responseMock->expects($this->once())
            ->method('getXResponseCode')
            ->willReturn($responseCode);
        $this->responseMock->expects($this->once())->method('getXResponseReasonText')->willReturn($reasonText);
        $this->dataHelperMock->expects($this->once())
            ->method('wrapGatewayError')
            ->with($reasonText)
            ->willReturn(__('Gateway error: %1', $reasonText));

        $this->paymentFailures->expects($this->exactly($failuresHandlerCalls))->method('handle')->with(1);
        $orderMock = $this->getOrderMock($failuresHandlerCalls);

        $orderMock->expects($this->exactly($failuresHandlerCalls))->method('getQuoteId')->willReturn(1);
        $reflection = new ReflectionClass($this->directpost);
        $order = $reflection->getProperty('order');
        $order->setAccessible(true);
        $order->setValue($this->directpost, $orderMock);

        $this->directpost->checkResponseCode();
    }

    /**
     * @return array
     */
    public function checkResponseCodeFailureDataProvider(): array
    {
        return [
            ['responseCode' => Directpost::RESPONSE_CODE_DECLINED, 1],
            ['responseCode' => Directpost::RESPONSE_CODE_ERROR, 1],
        ];
    }

    /**
     * @param bool $isInitializeNeeded
     *
     * @dataProvider setIsInitializeNeededDataProvider
     */
    public function testSetIsInitializeNeeded($isInitializeNeeded)
    {
        $this->directpost->setIsInitializeNeeded($isInitializeNeeded);
        $this->assertEquals($isInitializeNeeded, $this->directpost->isInitializeNeeded());
    }

    /**
     * @return array
     */
    public function setIsInitializeNeededDataProvider()
    {
        return [
            ['isInitializationNeeded' => true],
            ['isInitializationNeeded' => false]
        ];
    }

    /**
     * @param bool $isGatewayActionsLocked
     * @param bool $canCapture
     *
     * @dataProvider canCaptureDataProvider
     */
    public function testCanCapture($isGatewayActionsLocked, $canCapture)
    {
        $this->directpost->setData('info_instance', $this->paymentMock);

        $this->paymentMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with(Directpost::GATEWAY_ACTIONS_LOCKED_STATE_KEY)
            ->willReturn($isGatewayActionsLocked);

        $this->assertEquals($canCapture, $this->directpost->canCapture());
    }

    /**
     * @return array
     */
    public function canCaptureDataProvider()
    {
        return [
            ['isGatewayActionsLocked' => false, 'canCapture' => true],
            ['isGatewayActionsLocked' => true, 'canCapture' => false]
        ];
    }

    /**
     * @covers       \Magento\Authorizenet\Model\Directpost::fetchTransactionInfo
     *
     * @param $transactionId
     * @param $resultStatus
     * @param $responseStatus
     * @param $responseCode
     * @return void
     *
     * @dataProvider dataProviderTransaction
     */
    public function testFetchVoidedTransactionInfo($transactionId, $resultStatus, $responseStatus, $responseCode)
    {
        $paymentId = 36;
        $orderId = 36;

        $this->paymentMock->expects($this->once())->method('getId')->willReturn($paymentId);

        $orderMock = $this->getOrderMock();
        $orderMock->expects($this->once())->method('getId')->willReturn($orderId);
        $this->paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $transactionMock = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $this->transactionRepositoryMock->expects($this->once())
            ->method('getByTransactionId')
            ->with($transactionId, $paymentId, $orderId)
            ->willReturn($transactionMock);
        $document = $this->getTransactionXmlDocument(
            $transactionId,
            TransactionService::PAYMENT_UPDATE_STATUS_CODE_SUCCESS,
            $resultStatus,
            $responseStatus,
            $responseCode
        );
        $this->transactionServiceMock->expects($this->once())
            ->method('getTransactionDetails')
            ->with($this->directpost, $transactionId)
            ->willReturn($document);

        // transaction should be closed
        $this->paymentMock->expects($this->once())->method('setIsTransactionDenied')->with(true);
        $this->paymentMock->expects($this->once())->method('setIsTransactionClosed')->with(true);
        $transactionMock->expects($this->once())->method('close');

        $this->directpost->fetchTransactionInfo($this->paymentMock, $transactionId);
    }

    /**
     * @covers \Magento\Authorizenet\Model\Directpost::refund()
     * @return void
     */
    public function testSuccessRefund()
    {
        $card = 1111;

        $this->paymentMock->expects($this->exactly(1))->method('getCcLast4')->willReturn($card);
        $this->paymentMock->expects($this->once())->method('decrypt')->willReturn($card);
        $this->paymentMock->expects($this->exactly(3))
            ->method('getParentTransactionId')
            ->willReturn(self::TRANSACTION_ID . '-capture');
        $this->paymentMock->expects($this->once())->method('getPoNumber')->willReturn(self::INVOICE_NUM);
        $this->paymentMock->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(true)
            ->willReturnSelf();

        $this->addRequestMockToRequestFactoryMock();

        $orderMock = $this->getOrderMock();

        $orderMock->expects($this->once())->method('getId')->willReturn(1);
        $orderMock->expects($this->exactly(2))->method('getIncrementId')->willReturn(self::INVOICE_NUM);
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(1);

        $this->paymentMock->expects($this->exactly(2))->method('getOrder')->willReturn($orderMock);

        $transactionMock = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdditionalInformation'])
            ->getMock();
        $transactionMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with(Directpost::REAL_TRANSACTION_ID_KEY)
            ->willReturn(self::TRANSACTION_ID);

        $this->transactionRepositoryMock->expects($this->once())
            ->method('getByTransactionId')
            ->willReturn($transactionMock);

        $this->postRequestTest();

        $this->directpost->refund($this->paymentMock, self::TOTAL_AMOUNT);
    }

    /**
     * Get data for tests
     * @return array
     */
    public function dataProviderTransaction()
    {
        return [
            [
                'transactionId' => '9941997799',
                'resultStatus' => 'Successful.',
                'responseStatus' => 'voided',
                'responseCode' => 1
            ]
        ];
    }

    /**
     * Get transaction data
     * @param $transactionId
     * @param $resultCode
     * @param $resultStatus
     * @param $responseStatus
     * @param $responseCode
     * @return Element
     */
    private function getTransactionXmlDocument(
        $transactionId,
        $resultCode,
        $resultStatus,
        $responseStatus,
        $responseCode
    ) {
        $body = sprintf(
            '<?xml version="1.0" encoding="utf-8"?>
            <getTransactionDetailsResponse
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                    xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <messages>
                    <resultCode>%s</resultCode>
                    <message>
                        <code>I00001</code>
                        <text>%s</text>
                    </message>
                </messages>
                <transaction>
                    <transId>%s</transId>
                    <transactionType>authOnlyTransaction</transactionType>
                    <transactionStatus>%s</transactionStatus>
                    <responseCode>%s</responseCode>
                    <responseReasonCode>%s</responseReasonCode>
                </transaction>
            </getTransactionDetailsResponse>',
            $resultCode,
            $resultStatus,
            $transactionId,
            $responseStatus,
            $responseCode,
            $responseCode
        );
        libxml_use_internal_errors(true);
        $document = new Element($body);
        libxml_use_internal_errors(false);
        return $document;
    }

    /**
     * Get mock for authorize.net request factory
     */
    private function addRequestMockToRequestFactoryMock()
    {
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
        $this->requestFactory->expects($this->once())
            ->method('create')
            ->willReturn($request);
    }

    /**
     * Get mock for order
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getOrderMock()
    {
        return $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getQuoteId',
                    'getIncrementId',
                    'getStoreId',
                    'getBillingAddress',
                    'getShippingAddress',
                    'getBaseCurrencyCode',
                    'getBaseTaxAmount',
                    '__wakeup'
                ]
            )
            ->getMock();
    }

    /**
     * Get mocked response for refund transaction
     * @param $code
     * @param $reasonCode
     * @param $reasonText
     * @return string
     */
    private function getRefundResponseBody($code, $reasonCode, $reasonText)
    {
        $result = array_fill(0, 50, '');
        $result[0] = $code; // XResponseCode
        $result[2] = $reasonCode; // XResponseReasonCode
        $result[3] = $reasonText; // XResponseReasonText
        $result[6] = self::TRANSACTION_ID; // XTransId
        $result[7] = self::INVOICE_NUM; // XInvoiceNum
        $result[9] = self::TOTAL_AMOUNT; // XAmount
        $result[10] = Directpost::REQUEST_METHOD_CC; // XMethod
        $result[11] = Directpost::REQUEST_TYPE_CREDIT; // XType
        // @codingStandardsIgnoreStart
        $result[37] = md5(self::TRANSACTION_ID); // x_MD5_Hash
        // @codingStandardsIgnoreEnd
        $result[50] = '48329483921'; // setXAccountNumber
        return implode(Directpost::RESPONSE_DELIM_CHAR, $result);
    }
}
