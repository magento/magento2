<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response;

use Magento\Framework\DataObject;
use Magento\Paypal\Model\Payflowpro;
use Magento\Paypal\Model\Payflow\Service\Response\Transaction;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface;

/**
 * Test class for \Magento\Paypal\Model\Payflow\Service\Response\Transaction
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Transaction
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Session\Generic
     */
    protected $sessionTransparent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Paypal\Model\Payflow\Transparent
     */
    protected $transparent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagementInterface;

    /**
     * @var HandlerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $errorHandlerMock;

    /**
     * @var \Magento\Payment\Model\Method\Logger | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    protected function setUp()
    {
        $this->sessionTransparent = $this->getMock(
            \Magento\Framework\Session\Generic::class,
            ['getQuoteId'],
            [],
            '',
            false
        );
        $this->quoteRepository = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->transparent = $this->getMock(\Magento\Paypal\Model\Payflow\Transparent::class, [], [], '', false);
        $this->paymentMethodManagementInterface = $this->getMock(
            \Magento\Quote\Api\PaymentMethodManagementInterface::class,
            [],
            [],
            '',
            false
        );
        $this->errorHandlerMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface::class
        )->getMock();

        $this->loggerMock = $this->getMockBuilder(\Magento\Payment\Model\Method\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Transaction(
            $this->sessionTransparent,
            $this->quoteRepository,
            $this->transparent,
            $this->paymentMethodManagementInterface,
            $this->errorHandlerMock,
            $this->loggerMock
        );
    }

    public function testGetResponseObject()
    {
        $gatewayTransactionResponse = [];
        $result = new \Magento\Framework\DataObject();

        $this->transparent->expects($this->once())
            ->method('getDebugReplacePrivateDataKeys')
            ->willReturn(['key1', 'key2']);
        $this->transparent->expects($this->once())
            ->method('getDebugFlag')
            ->willReturn(true);

        $this->transparent->expects($this->once())
            ->method('mapGatewayResponse')
            ->with($gatewayTransactionResponse, $result)
            ->willReturn($result);

        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with($gatewayTransactionResponse, ['key1', 'key2'], true);

        $this->assertEquals($result, $this->model->getResponseObject($gatewayTransactionResponse));
    }

    public function testSavePaymentInQuote()
    {
        $quoteId = 1;
        $response = new DataObject();

        $payment = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payment->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('pnref');
        $this->errorHandlerMock->expects($this->once())
            ->method('handle')
            ->with($payment, $response);
        $quote = $this->getMock(\Magento\Quote\Api\Data\CartInterface::class, [], [], '', false);
        $quote->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($quoteId);

        $this->sessionTransparent->expects($this->once())
            ->method('getQuoteId')
            ->willReturn($quoteId);

        $this->quoteRepository->expects($this->once())
            ->method('get')
            ->willReturn($quote);

        $this->paymentMethodManagementInterface->expects($this->once())
            ->method('get')
            ->willReturn($payment);
        $this->paymentMethodManagementInterface->expects($this->once())
            ->method('set');

        $this->model->savePaymentInQuote($response);
    }
}
