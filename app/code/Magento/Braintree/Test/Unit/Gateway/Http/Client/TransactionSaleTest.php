<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Http\Client;

use Magento\Braintree\Gateway\Http\Client\TransactionSale;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
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
     * @var Logger|MockObject
     */
    private $logger;

    /**
     * @var BraintreeAdapter|MockObject
     */
    private $adapter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        /** @var LoggerInterface|MockObject $criticalLogger */
        $criticalLogger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var BraintreeAdapterFactory|MockObject $adapterFactory */
        $adapterFactory = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactory->method('create')
            ->willReturn($this->adapter);

        $this->model = new TransactionSale($criticalLogger, $this->logger, $this->adapter, $adapterFactory);
    }

    /**
     * Runs test placeRequest method (exception)
     *
     * @return void
     *
     * @expectedException \Magento\Payment\Gateway\Http\ClientException
     * @expectedExceptionMessage Test messages
     */
    public function testPlaceRequestException()
    {
        $this->logger->method('debug')
            ->with(
                [
                    'request' => $this->getTransferData(),
                    'client' => TransactionSale::class,
                    'response' => []
                ]
            );

        $this->adapter->method('sale')
            ->willThrowException(new \Exception('Test messages'));

        /** @var TransferInterface|MockObject $transferObjectMock */
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
        $this->adapter->method('sale')
            ->with($this->getTransferData())
            ->willReturn($response);

        $this->logger->method('debug')
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
     * Creates mock object for TransferInterface.
     *
     * @return TransferInterface|MockObject
     */
    private function getTransferObjectMock()
    {
        $transferObjectMock = $this->getMockForAbstractClass(TransferInterface::class);
        $transferObjectMock->method('getBody')
            ->willReturn($this->getTransferData());

        return $transferObjectMock;
    }

    /**
     * Creates stub for a response.
     *
     * @return \stdClass
     */
    private function getResponseObject()
    {
        $obj = new \stdClass;
        $obj->success = true;

        return $obj;
    }

    /**
     * Creates stub request data.
     *
     * @return array
     */
    private function getTransferData()
    {
        return [
            'test-data-key' => 'test-data-value'
        ];
    }
}
