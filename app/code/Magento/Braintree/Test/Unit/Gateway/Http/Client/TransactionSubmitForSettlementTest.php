<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Http\Client;

use Braintree\Result\Successful;
use Magento\Braintree\Gateway\Http\Client\TransactionSubmitForSettlement;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Tests \Magento\Braintree\Gateway\Http\Client\TransactionSubmitForSettlement.
 */
class TransactionSubmitForSettlementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransactionSubmitForSettlement
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

    protected function setUp(): void
    {
        /** @var LoggerInterface|MockObject $criticalLoggerMock */
        $criticalLoggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();

        $this->adapterMock = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['submitForSettlement'])
            ->getMock();
        /** @var BraintreeAdapterFactory|MockObject $adapterFactoryMock */
        $adapterFactoryMock = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactoryMock->method('create')
            ->willReturn($this->adapterMock);

        $this->client = new TransactionSubmitForSettlement(
            $criticalLoggerMock,
            $this->loggerMock,
            $adapterFactoryMock
        );
    }

    /**
     * @covers \Magento\Braintree\Gateway\Http\Client\TransactionSubmitForSettlement::placeRequest
     */
    public function testPlaceRequestWithException()
    {
        $this->expectException(\Magento\Payment\Gateway\Http\ClientException::class);
        $this->expectExceptionMessage('Transaction has been declined');

        $exception = new \Exception('Transaction has been declined');
        $this->adapterMock->expects(static::once())
            ->method('submitForSettlement')
            ->willThrowException($exception);

        /** @var TransferInterface|MockObject $transferObject */
        $transferObject = $this->getTransferObjectMock();
        $this->client->placeRequest($transferObject);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Http\Client\TransactionSubmitForSettlement::process
     */
    public function testPlaceRequest()
    {
        $data = new Successful(['success'], [true]);
        $this->adapterMock->expects(static::once())
            ->method('submitForSettlement')
            ->willReturn($data);

        /** @var TransferInterface|MockObject $transferObject */
        $transferObject = $this->getTransferObjectMock();
        $response = $this->client->placeRequest($transferObject);
        static::assertIsObject($response['object']);
        static::assertEquals(['object' => $data], $response);
    }

    /**
     * Creates mock for TransferInterface
     *
     * @return TransferInterface|MockObject
     */
    private function getTransferObjectMock()
    {
        $mock = $this->getMockForAbstractClass(TransferInterface::class);
        $mock->expects($this->once())
            ->method('getBody')
            ->willReturn([
                'transaction_id' => 'vb4c6b',
                'amount' => 124.00,
            ]);

        return $mock;
    }
}
