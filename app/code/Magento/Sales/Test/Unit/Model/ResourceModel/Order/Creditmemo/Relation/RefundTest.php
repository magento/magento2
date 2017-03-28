<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Creditmemo\Relation;

/**
 * Class RefundTest
 */
class RefundTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Relation\Refund
     */
    protected $refundResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->orderRepositoryMock = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceRepositoryMock = $this->getMockBuilder(\Magento\Sales\Api\InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceCurrencyMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->refundResource = $objectManager->getObject(
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Relation\Refund::class,
            [
                'orderRepository' => $this->orderRepositoryMock,
                'invoiceRepository' => $this->invoiceRepositoryMock,
                'priceCurrency' => $this->priceCurrencyMock
            ]
        );
    }

    public function testProcessRelation()
    {
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $creditmemoMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoMock->expects($this->once())
            ->method('getState')
            ->willReturn(\Magento\Sales\Model\Order\Creditmemo::STATE_REFUNDED);
        $creditmemoMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($orderMock);

        $this->assertNull($this->refundResource->processRelation($creditmemoMock));
    }
}
