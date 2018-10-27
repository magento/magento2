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
use PHPUnit_Framework_MockObject_MockObject as MockObject;
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

    protected function setUp()
    {
<<<<<<< HEAD
        /** @var LoggerInterface|MockObject $criticalLoggerMock */
        $criticalLoggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->loggerMock = $this->getMockBuilder(Logger::class)
=======
        /** @var LoggerInterface|MockObject $criticalLogger */
        $criticalLogger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->logger = $this->getMockBuilder(Logger::class)
>>>>>>> upstream/2.2-develop
            ->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();

<<<<<<< HEAD
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
=======
        $this->adapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['submitForSettlement'])
            ->getMock();
        /** @var BraintreeAdapterFactory|MockObject $adapterFactory */
        $adapterFactory = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactory->method('create')
            ->willReturn($this->adapter);

        $this->client = new TransactionSubmitForSettlement(
            $criticalLogger,
            $this->logger,
            $adapterFactory
>>>>>>> upstream/2.2-develop
        );
    }

    /**
     * @expectedException \Magento\Payment\Gateway\Http\ClientException
     * @expectedExceptionMessage Transaction has been declined
     */
    public function testPlaceRequestWithException()
    {
        $exception = new \Exception('Transaction has been declined');
<<<<<<< HEAD
        $this->adapterMock->expects(static::once())
            ->method('submitForSettlement')
=======
        $this->adapter->method('submitForSettlement')
>>>>>>> upstream/2.2-develop
            ->willThrowException($exception);

        /** @var TransferInterface|MockObject $transferObject */
        $transferObject = $this->getTransferObjectMock();
        $this->client->placeRequest($transferObject);
    }

    public function testPlaceRequest()
    {
        $data = new Successful(['success'], [true]);
<<<<<<< HEAD
        $this->adapterMock->expects(static::once())
            ->method('submitForSettlement')
=======
        $this->adapter->method('submitForSettlement')
>>>>>>> upstream/2.2-develop
            ->willReturn($data);

        /** @var TransferInterface|MockObject $transferObject */
        $transferObject = $this->getTransferObjectMock();
        $response = $this->client->placeRequest($transferObject);
        static::assertTrue(is_object($response['object']));
        static::assertEquals(['object' => $data], $response);
    }

    /**
     * Creates mock for TransferInterface
     *
     * @return TransferInterface|MockObject
     */
    private function getTransferObjectMock()
    {
        $mock = $this->createMock(TransferInterface::class);
        $mock->method('getBody')
            ->willReturn([
                'transaction_id' => 'vb4c6b',
                'amount' => 124.00,
            ]);

        return $mock;
    }
}
