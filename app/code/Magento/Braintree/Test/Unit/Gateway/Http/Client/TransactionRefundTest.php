<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Http\Client;

use Magento\Braintree\Gateway\Http\Client\TransactionRefund;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use Psr\Log\LoggerInterface;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;

/**
 * Tests \Magento\Braintree\Gateway\Http\Client\TransactionRefund.
 */
class TransactionRefundTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransactionRefund
     */
    private $client;

    /**
     * @var Logger|MockObject
     */
    private $loggerMock;

    /**
     * @var BraintreeAdapter|MockObject
     */
    private $adapterMock;

    /**
     * @var string
     */
    private $transactionId = 'px4kpev5';

    /**
     * @var string
     */
    private $paymentAmount = '100.00';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        /** @var LoggerInterface|MockObject $criticalLoggerMock */
        $criticalLoggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapterMock = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var BraintreeAdapterFactory|MockObject $adapterFactoryMock */
        $adapterFactoryMock = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->adapterMock);

        $this->client = new TransactionRefund($criticalLoggerMock, $this->loggerMock, $adapterFactoryMock);
    }

    /**
     * @return void
     *
     */
    public function testPlaceRequestException()
    {
        $this->expectException(\Magento\Payment\Gateway\Http\ClientException::class);
        $this->expectExceptionMessage('Test messages');

        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with(
                [
                    'request' => $this->getTransferData(),
                    'client' => TransactionRefund::class,
                    'response' => [],
                ]
            );

        $this->adapterMock->expects($this->once())
            ->method('refund')
            ->with($this->transactionId, $this->paymentAmount)
            ->willThrowException(new \Exception('Test messages'));

        /** @var TransferInterface|MockObject $transferObjectMock */
        $transferObjectMock = $this->getTransferObjectMock();

        $this->client->placeRequest($transferObjectMock);
    }

    /**
     * @return void
     */
    public function testPlaceRequestSuccess()
    {
        $response = new \stdClass;
        $response->success = true;
        $this->adapterMock->expects($this->once())
            ->method('refund')
            ->with($this->transactionId, $this->paymentAmount)
            ->willReturn($response);

        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with(
                [
                    'request' => $this->getTransferData(),
                    'client' => TransactionRefund::class,
                    'response' => ['success' => 1],
                ]
            );

        $actualResult = $this->client->placeRequest($this->getTransferObjectMock());

        $this->assertIsObject($actualResult['object']);
        $this->assertEquals(['object' => $response], $actualResult);
    }

    /**
     * Creates mock object for TransferInterface.
     *
     * @return TransferInterface|MockObject
     */
    private function getTransferObjectMock()
    {
        $transferObjectMock = $this->getMockForAbstractClass(TransferInterface::class);
        $transferObjectMock->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getTransferData());

        return $transferObjectMock;
    }

    /**
     * Creates stub request data.
     *
     * @return array
     */
    private function getTransferData()
    {
        return [
            'transaction_id' => $this->transactionId,
            PaymentDataBuilder::AMOUNT => $this->paymentAmount,
        ];
    }
}
