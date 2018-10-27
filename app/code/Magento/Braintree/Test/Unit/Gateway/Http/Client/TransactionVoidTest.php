<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
namespace Magento\Braintree\Test\Unit\Gateway\Http\Client;

use Magento\Braintree\Gateway\Http\Client\TransactionVoid;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
=======
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Gateway\Http\Client;

use Braintree\Result\Successful;
use Magento\Braintree\Gateway\Http\Client\TransactionVoid;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ConverterException;
>>>>>>> upstream/2.2-develop
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
<<<<<<< HEAD
 * Tests \Magento\Braintree\Gateway\Http\Client\TransactionVoid.
=======
 * Class TransactionVoidTest
>>>>>>> upstream/2.2-develop
 */
class TransactionVoidTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransactionVoid
     */
<<<<<<< HEAD
    private $client;

    /**
     * @var Logger|MockObject
     */
    private $loggerMock;

    /**
     * @var BraintreeAdapter|MockObject
=======
    private $transactionVoidModel;

    /**
     * @var BraintreeAdapter|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> upstream/2.2-develop
     */
    private $adapterMock;

    /**
<<<<<<< HEAD
     * @var string
     */
    private $transactionId = 'px4kpev5';

    /**
=======
>>>>>>> upstream/2.2-develop
     * @inheritdoc
     */
    protected function setUp()
    {
        /** @var LoggerInterface|MockObject $criticalLoggerMock */
        $criticalLoggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
<<<<<<< HEAD
        $this->loggerMock = $this->getMockBuilder(Logger::class)
=======
        /** @var Logger|\PHPUnit_Framework_MockObject_MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
>>>>>>> upstream/2.2-develop
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapterMock = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var BraintreeAdapterFactory|MockObject $adapterFactoryMock */
        $adapterFactoryMock = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
<<<<<<< HEAD
        $adapterFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->adapterMock);

        $this->client = new TransactionVoid($criticalLoggerMock, $this->loggerMock, $adapterFactoryMock);
    }

    /**
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
                    'client' => TransactionVoid::class,
                    'response' => [],
                ]
            );

        $this->adapterMock->expects($this->once())
            ->method('void')
            ->with($this->transactionId)
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
            ->method('void')
            ->with($this->transactionId)
            ->willReturn($response);

        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with(
                [
                    'request' => $this->getTransferData(),
                    'client' => TransactionVoid::class,
                    'response' => ['success' => 1],
                ]
            );

        $actualResult = $this->client->placeRequest($this->getTransferObjectMock());

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
        $transferObjectMock = $this->createMock(TransferInterface::class);
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
        ];
=======
        $adapterFactoryMock->method('create')
            ->willReturn($this->adapterMock);

        $this->transactionVoidModel = new TransactionVoid($criticalLoggerMock, $loggerMock, $adapterFactoryMock);
    }

    /**
     * @throws ClientException
     * @throws ConverterException
     */
    public function testVoidRequestWithStoreId()
    {
        $transactionId = '11223344';
        $data = [
            'store_id' => 0,
            'transaction_id' => $transactionId
        ];
        $successfulResponse = new Successful();

        /** @var TransferInterface|\PHPUnit_Framework_MockObject_MockObject $transferObjectMock */
        $transferObjectMock = $this->createMock(TransferInterface::class);
        $transferObjectMock->method('getBody')
            ->willReturn($data);
        $this->adapterMock->expects($this->once())
            ->method('void')
            ->with($transactionId)
            ->willReturn($successfulResponse);

        $response = $this->transactionVoidModel->placeRequest($transferObjectMock);

        self::assertEquals($successfulResponse, $response['object']);
>>>>>>> upstream/2.2-develop
    }
}
