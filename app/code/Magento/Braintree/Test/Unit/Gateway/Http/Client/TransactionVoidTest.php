<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Gateway\Http\Client;

use Braintree\Result\Successful;
use Magento\Braintree\Gateway\Http\Client\TransactionVoid;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class TransactionVoidTest
 */
class TransactionVoidTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransactionVoid
     */
    private $transactionVoidModel;

    /**
     * @var BraintreeAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        /** @var LoggerInterface|MockObject $criticalLoggerMock */
        $criticalLoggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        /** @var Logger|\PHPUnit_Framework_MockObject_MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapterMock = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var BraintreeAdapterFactory|MockObject $adapterFactoryMock */
        $adapterFactoryMock = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
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
    }
}
