<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Http\Client;

use Magento\BraintreeTwo\Model\Adapter\BraintreeTransaction;
use Magento\Payment\Model\Method\Logger;
use Magento\BraintreeTwo\Gateway\Http\Client\TransactionSale;
use Magento\Payment\Gateway\Http\TransferInterface;
use Psr\Log\LoggerInterface;

/**
 * Class TransactionSaleTest
 */
class TransactionSaleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransactionSale
     */
    private $model;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var BraintreeTransaction|\PHPUnit_Framework_MockObject_MockObject
     */
    private $braintreeTransactionMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $criticalLoggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeTransactionMock = $this->getMockBuilder(BraintreeTransaction::class)
            ->getMock();

        $this->model = new TransactionSale($criticalLoggerMock, $this->loggerMock, $this->braintreeTransactionMock);
    }

    /**
     * Run test placeRequest method (exception)
     *
     * @return void
     *
     * @expectedException \Magento\Payment\Gateway\Http\ClientException
     * @expectedExceptionMessage Test messages
     */
    public function testPlaceRequestException()
    {
        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with(
                [
                    'request' => $this->getTransferData(),
                    'client' => TransactionSale::class,
                    'response' => []
                ]
            );

        $this->braintreeTransactionMock->expects($this->once())
            ->method('sale')
            ->willThrowException(new \Exception('Test messages'));

        /** @var TransferInterface|\PHPUnit_Framework_MockObject_MockObject $transferObjectMock */
        $transferObjectMock = $this->getTransferObjectMock();

        $this->model->placeRequest($transferObjectMock);
    }

    /**
     * Run test placeRequest method
     *
     * @return void
     */
    public function testPlaceRequestSuccess()
    {
        $response = $this->getResponseObject();
        $this->braintreeTransactionMock->expects($this->once())
            ->method('sale')
            ->with($this->getTransferData())
            ->willReturn($response)
        ;

        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with(
                [
                    'request' => $this->getTransferData(),
                    'client' => TransactionSale::class,
                    'response' => ['success' => 1]
                ]
            );

        $actualResult = $this->model->placeRequest($this->getTransferObjectMock());

        $this->assertTrue(is_object($actualResult['object']));
        $this->assertEquals(['object' => $response], $actualResult);
    }

    /**
     * @return TransferInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTransferObjectMock()
    {
        $transferObjectMock = $this->getMock(TransferInterface::class);
        $transferObjectMock->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getTransferData());

        return $transferObjectMock;
    }

    /**
     * @return \stdClass
     */
    private function getResponseObject()
    {
        $obj = new \stdClass;
        $obj->success = true;

        return $obj;
    }

    /**
     * @return array
     */
    private function getTransferData()
    {
        return [
            'test-data-key' => 'test-data-value'
        ];
    }
}
