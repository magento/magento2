<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Simplexml\Element;
use Magento\Authorizenet\Model\TransactionService;

class TransactionServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\HTTP\ZendClient|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $httpClientMock;

    /**
     * @var \Magento\Authorizenet\Model\Authorizenet|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $authorizenetMock;

    /**
     * @var \Magento\Authorizenet\Model\TransactionService
     */
    protected $transactionService;

    protected function setUp(): void
    {
        $httpClientFactoryMock = $this->getHttpClientFactoryMock();

        $this->authorizenetMock = $this->getMockBuilder(\Magento\Authorizenet\Model\Directpost::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authorizenetMock->method('getConfigData')
            ->willReturnMap([
                ['login', 'test login'],
                ['trans_key', 'test key'],
                ['cgi_url_td', 'https://apitest.authorize.net/xml/v1/request.api']
            ]);

        $objectManagerHelper = new ObjectManager($this);
        $xmlSecurity = $objectManagerHelper->getObject(\Magento\Framework\Xml\Security::class);
        $this->transactionService = $objectManagerHelper->getObject(
            \Magento\Authorizenet\Model\TransactionService::class,
            [
                'xmlSecurityHelper' => $xmlSecurity,
                'httpClientFactory' => $httpClientFactoryMock
            ]
        );
    }

    /**
     * @covers \Magento\Authorizenet\Model\TransactionService::loadTransactionDetails
     * @param $transactionId
     * @param $resultStatus
     * @param $responseStatus
     * @param $responseCode
     * @return void
     *
     * @dataProvider dataProviderTransaction
     */
    public function testLoadVoidedTransactionDetails($transactionId, $resultStatus, $responseStatus, $responseCode)
    {
        $document = $this->getResponseBody(
            $transactionId,
            TransactionService::PAYMENT_UPDATE_STATUS_CODE_SUCCESS,
            $resultStatus,
            $responseStatus,
            $responseCode
        );
        $this->httpClientMock->expects(static::once())
            ->method('getBody')
            ->willReturn($document);

        $result = $this->transactionService->getTransactionDetails($this->authorizenetMock, $transactionId);

        static::assertEquals($responseCode, (string)$result->transaction->responseCode);
        static::assertEquals($responseCode, (string)$result->transaction->responseReasonCode);
        static::assertEquals($responseStatus, (string)$result->transaction->transactionStatus);
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
     * Create and return mock for http client factory
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getHttpClientFactoryMock()
    {
        $this->httpClientMock = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['request', 'getBody', '__wakeup'])
            ->getMock();

        $this->httpClientMock->expects(static::once())
            ->method('request')
            ->willReturnSelf();

        $httpClientFactoryMock = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $httpClientFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->httpClientMock);
        return $httpClientFactoryMock;
    }

    /**
     * Get body for xml request
     * @param string $transactionId
     * @param int $resultCode
     * @param string $resultStatus
     * @param string $responseStatus
     * @param string $responseCode
     * @return string
     */
    private function getResponseBody($transactionId, $resultCode, $resultStatus, $responseStatus, $responseCode)
    {
        return sprintf(
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
    }
}
