<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

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
    protected $_paymentMock;

    protected function setUp()
    {
        $helperManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->orderMock = $this->getMockBuilder(
            'Magento\Sales\Model\Order'
        )->disableOriginalConstructor()->setMethods(
            ['getPayment', '__wakeup', 'load', 'setHistoryEntityName']
        )->getMock();
        $this->_paymentMock = $this->getMockBuilder(
            'Magento\Sales\Model\Order\Payment'
        )->disableOriginalConstructor()->setMethods(
            ['canVoid', '__wakeup']
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
        $this->model = $helperManager->getObject('Magento\Sales\Model\Order\Invoice', $arguments);
        $this->model->setOrder($this->orderMock);
    }

    /**
     * @dataProvider canVoidDataProvider
     * @param bool $canVoid
     */
    public function testCanVoid($canVoid)
    {
        $entityName = 'invoice';
        $this->orderMock->expects($this->once())->method('getPayment')->will($this->returnValue($this->_paymentMock));
        $this->orderMock->expects($this->once())
            ->method('setHistoryEntityName')
            ->with($entityName)
            ->will($this->returnSelf());
        $this->_paymentMock->expects(
            $this->once()
        )->method(
            'canVoid',
            '__wakeup'
        )->with(
            $this->equalTo($this->model)
        )->will(
            $this->returnValue($canVoid)
        );

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
        $orderId = 100000041;
        $this->model->setOrderId($orderId);
        $entityName = 'invoice';
        $this->orderMock->expects($this->atLeastOnce())
            ->method('setHistoryEntityName')
            ->with($entityName)
            ->will($this->returnSelf());

        $this->assertEquals($this->orderMock, $this->model->getOrder());
    }

    public function testGetEntityType()
    {
        $this->assertEquals('invoice', $this->model->getEntityType());
    }

    public function testGetIncrementId()
    {
        $this->model->setIncrementId('test_increment_id');
        $this->assertEquals('test_increment_id', $this->model->getIncrementId());
    }
}
