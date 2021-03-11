<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Paypal\Model\Payflow;

class PayflowExpressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Paypal\Model\PayflowExpress
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $transactionRepository;

    /**
     * Payflow pro transaction key
     */
    const TRANSPORT_PAYFLOW_TXN_ID = 'Payflow pro transaction key';

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $proFactory = $this->getMockBuilder(
            \Magento\Paypal\Model\ProFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $api = $this->createMock(\Magento\Paypal\Model\Api\Nvp::class);
        $paypalPro = $this->getMockBuilder(
            \Magento\Paypal\Model\Pro::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $this->transactionRepository = $this->getMockBuilder(\Magento\Sales\Api\TransactionRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByTransactionType'])
            ->getMockForAbstractClass();
        $paypalPro->expects($this->any())->method('getApi')->willReturn($api);

        $proFactory->expects($this->once())->method('create')->willReturn($paypalPro);

        $this->_model = $objectManager->getObject(
            \Magento\Paypal\Model\PayflowExpress::class,
            ['proFactory' => $proFactory, 'transactionRepository' => $this->transactionRepository]
        );
    }

    public function testCanRefundCaptureNotExist()
    {
        $paymentInfo = $this->_getPreparedPaymentInfo();
        $paymentInfo->expects($this->once())->method('getOrder')->willReturnSelf();
        $this->transactionRepository->expects($this->once())
            ->method('getByTransactionType')
            ->with(Transaction::TYPE_CAPTURE)
            ->willReturn(false);
        $this->assertFalse($this->_model->canRefund());
    }

    public function testCanRefundCaptureExistNoAdditionalInfo()
    {
        $paymentInfo = $this->_getPreparedPaymentInfo();
        $captureTransaction = $this->_getCaptureTransaction();
        $captureTransaction->expects($this->once())->method('getAdditionalInformation')->with(
            Payflow\Pro::TRANSPORT_PAYFLOW_TXN_ID
        )->willReturn(null);
        $paymentInfo->expects($this->once())->method('getOrder')->willReturnSelf();
        $this->transactionRepository->expects($this->once())
            ->method('getByTransactionType')
            ->with(Transaction::TYPE_CAPTURE)
            ->willReturn($captureTransaction);
        $this->assertFalse($this->_model->canRefund());
    }

    public function testCanRefundCaptureExistValid()
    {
        $paymentInfo = $this->_getPreparedPaymentInfo();
        $captureTransaction = $this->_getCaptureTransaction();
        $captureTransaction->expects($this->once())->method('getAdditionalInformation')->with(
            Payflow\Pro::TRANSPORT_PAYFLOW_TXN_ID
        )->willReturn(self::TRANSPORT_PAYFLOW_TXN_ID);
        $paymentInfo->expects($this->once())->method('getOrder')->willReturnSelf();
        $this->transactionRepository->expects($this->once())
            ->method('getByTransactionType')
            ->with(Transaction::TYPE_CAPTURE)
            ->willReturn($captureTransaction);
        $this->assertTrue($this->_model->canRefund());
    }

    /**
     * Prepares payment info mock and adds it to the model
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function _getPreparedPaymentInfo()
    {
        $paymentInfo = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Payment::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $this->_model->setData('info_instance', $paymentInfo);
        return $paymentInfo;
    }

    /**
     * Prepares capture transaction
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function _getCaptureTransaction()
    {
        return $this->getMockBuilder(
            \Magento\Sales\Model\Order\Payment\Transaction::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
    }

    public function testCanFetchTransactionInfo()
    {
        $this->assertFalse($this->_model->canFetchTransactionInfo());
    }

    public function testCanReviewPayment()
    {
        $this->assertFalse($this->_model->canReviewPayment());
    }
}
