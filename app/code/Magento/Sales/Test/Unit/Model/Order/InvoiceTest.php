<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Resource\OrderFactory;

/**
 * Class InvoiceTest
 *
 * @package Magento\Sales\Model\Order
 */
class InvoiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $model;

    /**
     * Same as $model but Order was not set
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $modelWithoutOrder;

    /**
     * @var string
     */
    protected $entityType = 'invoice';

    /**
     * @var OrderFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order
     */
    protected $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order\Payment
     */
    protected $paymentMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $helperManager;

    protected function setUp()
    {
        $this->helperManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->orderMock = $this->getMockBuilder(
            'Magento\Sales\Model\Order'
        )->disableOriginalConstructor()->setMethods(
            [
                'getPayment', '__wakeup', 'load', 'setHistoryEntityName', 'getStore', 'getBillingAddress',
                'getShippingAddress'
            ]
        )->getMock();
        $this->orderMock->expects($this->any())
            ->method('setHistoryEntityName')
            ->with($this->entityType)
            ->will($this->returnSelf());

        $this->paymentMock = $this->getMockBuilder(
            'Magento\Sales\Model\Order\Payment'
        )->disableOriginalConstructor()->setMethods(
            ['canVoid', '__wakeup', 'canCapture']
        )->getMock();

        $this->orderFactory = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);

        $arguments = [
            'orderFactory' => $this->orderFactory,
            'orderResourceFactory' => $this->getMock(
                'Magento\Sales\Model\Resource\OrderFactory',
                [],
                [],
                '',
                false
            ),
            'calculatorFactory' => $this->getMock(
                    'Magento\Framework\Math\CalculatorFactory',
                    [],
                    [],
                    '',
                    false
                ),
            'invoiceItemCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\Resource\Order\Invoice\Item\CollectionFactory',
                [],
                [],
                '',
                false
            ),
            'invoiceCommentFactory' => $this->getMock(
                'Magento\Sales\Model\Order\Invoice\CommentFactory',
                [],
                [],
                '',
                false
            ),
            'commentCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\Resource\Order\Invoice\Comment\CollectionFactory',
                [],
                [],
                '',
                false
            ),
        ];
        $this->model = $this->helperManager->getObject('Magento\Sales\Model\Order\Invoice', $arguments);
        $this->model->setOrder($this->orderMock);
        $this->modelWithoutOrder = $this->helperManager->getObject('Magento\Sales\Model\Order\Invoice', $arguments);
    }

    /**
     * @dataProvider canVoidDataProvider
     * @param bool $canVoid
     */
    public function testCanVoid($canVoid)
    {
        $this->orderMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->paymentMock->expects($this->once())
            ->method('canVoid', '__wakeup')
            ->willReturn($canVoid);

        $this->model->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
        $this->assertEquals($canVoid, $this->model->canVoid());
    }

    /**
     * @dataProvider canVoidDataProvider
     * @param bool $canVoid
     */
    public function testDefaultCanVoid($canVoid)
    {
        $this->model->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
        $this->model->setCanVoidFlag($canVoid);

        $this->assertEquals($canVoid, $this->model->canVoid());
    }

    public function canVoidDataProvider()
    {
        return [[true], [false]];
    }

    public function testGetOrder()
    {
        $this->orderMock->expects($this->once())
            ->method('setHistoryEntityName')
            ->with($this->entityType)
            ->will($this->returnSelf());

        $this->assertEquals($this->orderMock, $this->model->getOrder());
    }

    public function testGetOrderLoadedById()
    {
        $orderId = 100000041;
        $this->modelWithoutOrder->setOrderId($orderId);
        $this->orderMock->expects($this->once())
            ->method('load')
            ->with($orderId)
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('setHistoryEntityName')
            ->with($this->entityType)
            ->willReturnSelf();
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->orderMock);

        $this->assertEquals($this->orderMock, $this->modelWithoutOrder->getOrder());
    }

    public function testGetEntityType()
    {
        $this->assertEquals($this->entityType, $this->model->getEntityType());
    }

    public function testGetIncrementId()
    {
        $this->model->setIncrementId('test_increment_id');
        $this->assertEquals('test_increment_id', $this->model->getIncrementId());
    }

    public function testSetOrder()
    {
        $orderId = 1111;
        $storeId = 2221;
        $this->orderMock->setId($orderId);
        $this->orderMock->setStoreId($storeId);
        $this->assertNull($this->model->getOrderId());
        $this->assertNull($this->model->getStoreId());

        $this->assertEquals($this->model, $this->model->setOrder($this->orderMock));
        $this->assertEquals($this->orderMock, $this->model->getOrder());
        $this->assertEquals($orderId, $this->model->getOrderId());
        $this->assertEquals($storeId, $this->model->getStoreId());
    }

    public function testGetStore()
    {
        $store = $this->helperManager->getObject('\Magento\Store\Model\Store', []);
        $this->orderMock->expects($this->once())->method('getStore')->willReturn($store);
        $this->assertEquals($store, $this->model->getStore());

    }

    public function testGetShippingAddress()
    {
        $address = $this->helperManager->getObject('\Magento\Sales\Model\Order\Address', []);
        $this->orderMock->expects($this->once())->method('getShippingAddress')->willReturn($address);
        $this->assertEquals($address, $this->model->getShippingAddress());

    }

    /**
     * @dataProvider canCaptureDataProvider
     * @param string $state
     * @param bool|null $canPaymentCapture
     * @param bool $expectedResult
     */
    public function testCanCapture($state, $canPaymentCapture, $expectedResult)
    {
        $this->model->setState($state);
        if (null !== $canPaymentCapture) {
            $this->orderMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
            $this->paymentMock->expects($this->once())->method('canCapture')->willReturn($canPaymentCapture);
        } else {
            $this->orderMock->expects($this->never())->method('getPayment');
            $this->paymentMock->expects($this->never())->method('canCapture');
        }
        $this->assertEquals($expectedResult, $this->model->canCapture());
    }

    /**
     * Data provider for testCanCapture
     *
     * @return array
     */
    public function canCaptureDataProvider()
    {
        return [
            [Invoice::STATE_OPEN, true, true],
            [Invoice::STATE_OPEN, false, false],
            [Invoice::STATE_CANCELED, null, false],
            [Invoice::STATE_CANCELED, null, false],
            [Invoice::STATE_PAID, null, false],
            [Invoice::STATE_PAID, null, false]
        ];
    }

    /**
     * @dataProvider canCancelDataProvider
     * @param string $state
     * @param bool $expectedResult
     */
    public function testCanCancel($state, $expectedResult)
    {
        $this->model->setState($state);
        $this->assertEquals($expectedResult, $this->model->canCancel());
    }

    /**
     * Data provider for testCanCancel
     *
     * @return array
     */
    public function canCancelDataProvider()
    {
        return [
            [Invoice::STATE_OPEN, true],
            [Invoice::STATE_CANCELED, false],
            [Invoice::STATE_PAID, false]
        ];
    }
}
