<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Payflow;
use Magento\Paypal\Model\PayflowExpress;
use Magento\Paypal\Model\Pro;
use Magento\Paypal\Model\ProFactory;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PayflowExpressTest extends TestCase
{
    /**
     * @var PayflowExpress
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $transactionRepository;

    /**
     * Payflow pro transaction key
     */
    const TRANSPORT_PAYFLOW_TXN_ID = 'Payflow pro transaction key';

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $proFactory = $this->getMockBuilder(
            ProFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])->getMock();
        $api = $this->createMock(Nvp::class);
        $paypalPro = $this->getMockBuilder(
            Pro::class
        )->disableOriginalConstructor()
            ->onlyMethods(['getApi','setMethod'])->getMock();
        $this->transactionRepository = $this->getMockBuilder(TransactionRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getByTransactionType'])
            ->getMockForAbstractClass();
        $paypalPro->expects($this->any())->method('getApi')->willReturn($api);

        $proFactory->expects($this->once())->method('create')->willReturn($paypalPro);

        $this->_model = $objectManager->getObject(
            PayflowExpress::class,
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
     * @return MockObject
     */
    protected function _getPreparedPaymentInfo()
    {
        $paymentInfo = $this->getMockBuilder(
            Payment::class
        )->disableOriginalConstructor()->getMock();
        $this->_model->setData('info_instance', $paymentInfo);
        return $paymentInfo;
    }

    /**
     * Prepares capture transaction
     *
     * @return MockObject
     */
    protected function _getCaptureTransaction()
    {
        return $this->getMockBuilder(
            Transaction::class
        )->disableOriginalConstructor()->getMock();
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
