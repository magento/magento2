<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Model;

use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\Framework\Simplexml\Element;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Authorizenet\Model\Directpost;
use Magento\Authorizenet\Model\TransactionService;
use Magento\Authorizenet\Model\Request;
use Magento\Authorizenet\Model\Directpost\Request\Factory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;

/**
 * Class DirectpostTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DirectpostTest extends \PHPUnit_Framework_TestCase
{
    const TOTAL_AMOUNT = 100.02;
    const INVOICE_NUM = '00000001';
    const TRANSACTION_ID = '41a23x34fd124';

    /**
     * @var \Magento\Authorizenet\Model\Directpost
     */
    protected $directpost;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Payment\Model\InfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Authorizenet\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Response\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseFactoryMock;

    /**
     * @var TransactionRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionRepositoryMock;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var TransactionService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionServiceMock;

    /**
     * @var \Magento\Framework\HTTP\ZendClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpClientMock;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Request\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestFactory;

    /**
     * @var PaymentFailuresInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentFailures;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMock();
        $this->paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->setMethods([
                'getOrder', 'getId', 'setAdditionalInformation', 'getAdditionalInformation',
                'setIsTransactionDenied', 'setIsTransactionClosed', 'decrypt', 'getCcLast4',
                'getParentTransactionId', 'getPoNumber'
            ])
            ->getMock();
        $this->dataHelperMock = $this->getMockBuilder('Magento\Authorizenet\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->initResponseFactoryMock();

        $this->transactionRepositoryMock = $this->getMockBuilder(
            'Magento\Sales\Model\Order\Payment\Transaction\Repository'
        )
            ->disableOriginalConstructor()
            ->setMethods(['getByTransactionId'])
            ->getMock();

        $this->transactionServiceMock = $this->getMockBuilder('Magento\Authorizenet\Model\TransactionService')
            ->disableOriginalConstructor()
            ->setMethods(['getTransactionDetails'])
            ->getMock();

        $this->paymentFailures = $this->getMockBuilder(
            PaymentFailuresInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestFactory = $this->getRequestFactoryMock();
        $httpClientFactoryMock = $this->getHttpClientFactoryMock();

        $helper = new ObjectManagerHelper($this);
        $this->directpost = $helper->getObject(
            'Magento\Authorizenet\Model\Directpost',
            [
                'scopeConfig' => $this->scopeConfigMock,
                'dataHelper' => $this->dataHelperMock,
                'requestFactory' => $this->requestFactory,
                'responseFactory' => $this->responseFactoryMock,
                'transactionRepository' => $this->transactionRepositoryMock,
                'transactionService' => $this->transactionServiceMock,
                'httpClientFactory' => $httpClientFactoryMock,
                'paymentFailures' => $this->paymentFailures
            ]
        );
    }

    public function testGetConfigInterface()
    {
        $this->assertInstanceOf(
            'Magento\Payment\Model\Method\ConfigInterface',
            $this->directpost->getConfigInterface()
        );
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

        $helperDataMock = $this->getMockBuilder('Magento\Authorizenet\Helper\Backend\Data')
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

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with('payment/authorizenet_directpost/payment_action', 'store', null)
            ->willReturn($paymentAction);
        $this->paymentMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('payment_type', $paymentAction);

        $this->directpost->authorize($this->paymentMock, 10);
    }

    public function testGetCgiUrl()
    {
        $url = 'cgi/url';

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with('payment/authorizenet_directpost/cgi_url', 'store', null)
            ->willReturn($url);

        $this->assertEquals($url, $this->directpost->getCgiUrl());
    }

    public function testGetCgiUrlWithEmptyConfigValue()
    {
        $this->scopeConfigMock->expects($this->any())
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

        $this->dataHelperMock->expects($this->any())
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
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    ['payment/authorizenet_directpost/trans_md5', 'store', null, $transMd5],
                    ['payment/authorizenet_directpost/login', 'store', null, $login]
                ]
            );
        $this->responseMock->expects($this->any())
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
     * @param bool $responseCode
     * @param int $failuresHandlerCalls
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider checkResponseCodeFailureDataProvider
     */
    public function testCheckResponseCodeFailure($responseCode, $failuresHandlerCalls)
    {
        $reasonText = 'reason text';

        $this->responseMock->expects($this->once())
            ->method('getXResponseCode')
            ->willReturn($responseCode);
        $this->responseMock->expects($this->any())
            ->method('getXResponseReasonText')
            ->willReturn($reasonText);
        $this->dataHelperMock->expects($this->any())
            ->method('wrapGatewayError')
            ->with($reasonText)
            ->willReturn(__('Gateway error: %1', $reasonText));

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock->expects($this->exactly($failuresHandlerCalls))
            ->method('getQuoteId')
            ->willReturn(1);

        $this->paymentFailures->expects($this->exactly($failuresHandlerCalls))
            ->method('handle')
            ->with(1);

        $reflection = new \ReflectionClass($this->directpost);
        $order = $reflection->getProperty('order');
        $order->setAccessible(true);
        $order->setValue($this->directpost, $orderMock);

        $this->directpost->checkResponseCode();
    }

    /**
     * @return array
     */
    public function checkResponseCodeFailureDataProvider()
    {
        return [
            ['responseCode' => Directpost::RESPONSE_CODE_DECLINED, 1],
            ['responseCode' => Directpost::RESPONSE_CODE_ERROR, 1],
            ['responseCode' => 999999, 0]
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

        $this->paymentMock->expects($this->any())
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

        $this->paymentMock->expects(static::once())
            ->method('getId')
            ->willReturn($paymentId);

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $orderMock->expects(static::once())
            ->method('getId')
            ->willReturn($orderId);

        $this->paymentMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $transactionMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment\Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionRepositoryMock->expects(static::once())
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
        $this->transactionServiceMock->expects(static::once())
            ->method('getTransactionDetails')
            ->with($this->directpost, $transactionId)
            ->willReturn($document);

        // transaction should be closed
        $this->paymentMock->expects(static::once())
            ->method('setIsTransactionDenied')
            ->with(true);
        $this->paymentMock->expects(static::once())
            ->method('setIsTransactionClosed')
            ->with(true);
        $transactionMock->expects(static::once())
            ->method('close');

        $this->directpost->fetchTransactionInfo($this->paymentMock, $transactionId);
    }

    /**
     * @covers \Magento\Authorizenet\Model\Directpost::refund()
     * @return void
     */
    public function testSuccessRefund()
    {
        $card = 1111;

        $this->paymentMock->expects(static::exactly(2))
            ->method('getCcLast4')
            ->willReturn($card);
        $this->paymentMock->expects(static::once())
            ->method('decrypt')
            ->willReturn($card);
        $this->paymentMock->expects(static::exactly(3))
            ->method('getParentTransactionId')
            ->willReturn(self::TRANSACTION_ID . '-capture');
        $this->paymentMock->expects(static::once())
            ->method('getPoNumber')
            ->willReturn(self::INVOICE_NUM);
        $this->paymentMock->expects(static::once())
            ->method('setIsTransactionClosed')
            ->with(true)
            ->willReturnSelf();

        $orderMock = $this->getOrderMock();

        $this->paymentMock->expects(static::exactly(2))
            ->method('getOrder')
            ->willReturn($orderMock);

        $transactionMock = $this->getMockBuilder(Order\Payment\Transaction::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdditionalInformation'])
            ->getMock();
        $transactionMock->expects(static::once())
            ->method('getAdditionalInformation')
            ->with(Directpost::REAL_TRANSACTION_ID_KEY)
            ->willReturn(self::TRANSACTION_ID);

        $this->transactionRepositoryMock->expects(static::once())
            ->method('getByTransactionId')
            ->willReturn($transactionMock);

        $response = $this->getRefundResponseBody(
            Directpost::RESPONSE_CODE_APPROVED,
            Directpost::RESPONSE_REASON_CODE_APPROVED,
            'Successful'
        );
        $this->httpClientMock->expects(static::once())
            ->method('getBody')
            ->willReturn($response);

        $this->responseMock->expects(static::once())
            ->method('getXResponseCode')
            ->willReturn(Directpost::RESPONSE_CODE_APPROVED);
        $this->responseMock->expects(static::once())
            ->method('getXResponseReasonCode')
            ->willReturn(Directpost::RESPONSE_REASON_CODE_APPROVED);

        $this->dataHelperMock->expects(static::never())
            ->method('wrapGatewayError');

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
     * Create mock for response factory
     * @return void
     */
    private function initResponseFactoryMock()
    {
        $this->responseFactoryMock = $this->getMockBuilder('Magento\Authorizenet\Model\Directpost\Response\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Authorizenet\Model\Directpost\Response')
            ->setMethods(
                [
                    'isValidHash',
                    'getXTransId', 'getXResponseCode', 'getXResponseReasonCode', 'getXResponseReasonText', 'getXAmount',
                    'setXResponseCode', 'setXResponseReasonCode', 'setXAvsCode', 'setXResponseReasonText',
                    'setXTransId', 'setXInvoiceNum', 'setXAmount', 'setXMethod', 'setXType', 'setData',
                    'setXAccountNumber',
                    '__wakeup'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock->expects(static::any())
            ->method('setXResponseCode')
            ->willReturnSelf();
        $this->responseMock->expects(static::any())
            ->method('setXResponseReasonCode')
            ->willReturnSelf();
        $this->responseMock->expects(static::any())
            ->method('setXResponseReasonText')
            ->willReturnSelf();
        $this->responseMock->expects(static::any())
            ->method('setXAvsCode')
            ->willReturnSelf();
        $this->responseMock->expects(static::any())
            ->method('setXTransId')
            ->willReturnSelf();
        $this->responseMock->expects(static::any())
            ->method('setXInvoiceNum')
            ->willReturnSelf();
        $this->responseMock->expects(static::any())
            ->method('setXAmount')
            ->willReturnSelf();
        $this->responseMock->expects(static::any())
            ->method('setXMethod')
            ->willReturnSelf();
        $this->responseMock->expects(static::any())
            ->method('setXType')
            ->willReturnSelf();
        $this->responseMock->expects(static::any())
            ->method('setData')
            ->willReturnSelf();

        $this->responseFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->responseMock);
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
     * @return \PHPUnit_Framework_MockObject_MockBuilder
     */
    private function getRequestFactoryMock()
    {
        $requestFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
        $requestFactory->expects(static::any())
            ->method('create')
            ->willReturn($request);
        return $requestFactory;
    }

    /**
     * Get mock for order
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getOrderMock()
    {
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getId', 'getIncrementId', 'getStoreId', 'getBillingAddress', 'getShippingAddress',
                'getBaseCurrencyCode', 'getBaseTaxAmount', '__wakeup'
            ])
            ->getMock();

        $orderMock->expects(static::once())
            ->method('getId')
            ->willReturn(1);

        $orderMock->expects(static::exactly(2))
            ->method('getIncrementId')
            ->willReturn(self::INVOICE_NUM);

        $orderMock->expects(static::once())
            ->method('getStoreId')
            ->willReturn(1);

        $orderMock->expects(static::once())
            ->method('getBaseCurrencyCode')
            ->willReturn('USD');
        return $orderMock;
    }

    /**
     * Create and return mock for http client factory
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getHttpClientFactoryMock()
    {
        $this->httpClientMock = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['request', 'getBody', '__wakeup'])
            ->getMock();

        $this->httpClientMock->expects(static::any())
            ->method('request')
            ->willReturnSelf();

        $httpClientFactoryMock = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $httpClientFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->httpClientMock);
        return $httpClientFactoryMock;
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
        $result[37] = md5(self::TRANSACTION_ID); // x_MD5_Hash
        $result[50] = '48329483921'; // setXAccountNumber
        return implode(Directpost::RESPONSE_DELIM_CHAR, $result);
    }
}
