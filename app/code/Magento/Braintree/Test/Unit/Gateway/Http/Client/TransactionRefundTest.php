<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Gateway\Http\Client;

use Braintree\Result\Successful;
use Magento\Braintree\Gateway\Http\Client\TransactionRefund;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Payment\Model\Method\Logger;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class TransactionRefundTest
 */
class TransactionRefundTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransactionRefund
     */
    private $transactionRefundModel;

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

        $this->transactionRefundModel = new TransactionRefund($criticalLoggerMock, $loggerMock, $adapterFactoryMock);
    }

    /**
     * @throws ClientException
     * @throws ConverterException
     */
    public function testRefundRequestWithStoreId()
    {
        $transactionId = '11223344';
        $refundAmount = 10;
        $data = [
            'store_id' => 0,
            'transaction_id' => $transactionId,
            PaymentDataBuilder::AMOUNT => $refundAmount
        ];
        $successfulResponse = new Successful();

        /** @var TransferInterface|\PHPUnit_Framework_MockObject_MockObject $transferObjectMock */
        $transferObjectMock = $this->createMock(TransferInterface::class);
        $transferObjectMock->method('getBody')
            ->willReturn($data);
        $this->adapterMock->expects($this->once())
            ->method('refund')
            ->with($transactionId, $refundAmount)
            ->willReturn($successfulResponse);

        $response = $this->transactionRefundModel->placeRequest($transferObjectMock);

        self::assertEquals($successfulResponse, $response['object']);
    }
}
