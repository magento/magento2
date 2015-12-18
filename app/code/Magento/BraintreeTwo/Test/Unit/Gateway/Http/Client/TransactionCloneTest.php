<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BraintreeTwo\Test\Unit\Gateway\Http\Client;

use Braintree\Result\Successful;
use Magento\BraintreeTwo\Gateway\Http\Client\TransactionClone;
use Magento\BraintreeTwo\Model\Adapter\BraintreeAdapter;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class TransactionCloneTest
 */
class TransactionCloneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransactionClone
     */
    private $client;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var BraintreeAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    protected function setUp()
    {
        $criticalLogger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->client = new TransactionClone($criticalLogger, $this->loggerMock, $this->adapter);
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Http\Client\TransactionSubmitForSettlement::placeRequest
     */
    public function testPlaceRequest()
    {
        $data = new Successful(['success'], [true]);
        $this->adapter->expects(static::once())
            ->method('cloneTransaction')
            ->willReturn($data);

        /** @var TransferInterface|\PHPUnit_Framework_MockObject_MockObject $transferObjectMock */
        $transferObjectMock = $this->getTransferObjectMock();
        $response = $this->client->placeRequest($transferObjectMock);
        static::assertTrue(is_object($response['object']));
        static::assertEquals(['object' => $data], $response);
    }

    /**
     * @return TransferInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTransferObjectMock()
    {
        $mock = $this->getMock(TransferInterface::class);
        $mock->expects(static::once())
            ->method('getBody')
            ->willReturn([
                'transaction_id' => 'vb4b7c6b',
                'amount' => 45.00
            ]);

        return $mock;
    }
}
