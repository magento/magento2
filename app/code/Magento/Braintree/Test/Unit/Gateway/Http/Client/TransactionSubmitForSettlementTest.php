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
 * Class TransactionSubmitForSettlementTest
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
    private $logger;

    /**
     * @var BraintreeAdapter|MockObject
     */
    private $adapter;

    protected function setUp()
    {
        /** @var LoggerInterface|MockObject $criticalLogger */
        $criticalLogger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();

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
        );
    }

    /**
     * @expectedException \Magento\Payment\Gateway\Http\ClientException
     * @expectedExceptionMessage Transaction has been declined
     */
    public function testPlaceRequestWithException()
    {
        $exception = new \Exception('Transaction has been declined');
        $this->adapter->method('submitForSettlement')
            ->willThrowException($exception);

        /** @var TransferInterface|MockObject $transferObject */
        $transferObject = $this->getTransferObjectMock();
        $this->client->placeRequest($transferObject);
    }

    public function testPlaceRequest()
    {
        $data = new Successful(['success'], [true]);
        $this->adapter->method('submitForSettlement')
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
                'amount' => 124.00
            ]);

        return $mock;
    }
}
