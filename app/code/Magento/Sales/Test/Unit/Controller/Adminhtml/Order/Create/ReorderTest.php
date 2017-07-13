<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Create\Reorder;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Reorder\UnavailableProductsProvider;
use Magento\Sales\Helper\Reorder as ReorderHelper;

/**
 * Class ReorderTest
 */
class ReorderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Reorder
     */
    private $reorder;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultForwardFactoryMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultForwardMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteSessionMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var ReorderHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reorderHelperMock;

    /**
     * @var UnavailableProductsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $unavailableProductsProviderMock;

    /**
     * @var Create|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderCreateMock;

    /**
     * @var int
     */
    private $orderId;

    protected function setUp()
    {
        $this->orderId = 111;
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)->getMockForAbstractClass();
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId', 'getId', 'setReordered'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)->getMockForAbstractClass();
        $this->resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)->disableOriginalConstructor()->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)->disableOriginalConstructor()->getMock();
        $this->quoteSessionMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['clearStorage', 'setUseOldShippingMethod'])
            ->getMock();
        $this->reorderHelperMock = $this->getMockBuilder(ReorderHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)->getMockForAbstractClass();
        $this->unavailableProductsProviderMock = $this->getMockBuilder(UnavailableProductsProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderCreateMock = $this->getMockBuilder(Create::class)->disableOriginalConstructor()->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'objectManager' => $this->objectManagerMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );

        $this->reorder = $objectManager->getObject(
            Reorder::class,
            [
                'unavailableProductsProvider' => $this->unavailableProductsProviderMock,
                'orderRepository' => $this->orderRepositoryMock,
                'reorderHelper' => $this->reorderHelperMock,
                'context' => $this->context,
                'resultForwardFactory' => $this->resultForwardFactoryMock,
            ]
        );
    }

    public function testExecuteForward()
    {
        $this->clearStorage();
        $this->getOrder();
        $this->canReorder(false);
        $this->prepareForward();

        $this->assertInstanceOf(Forward::class, $this->reorder->execute());
    }

    public function testExecuteRedirectOrderGrid()
    {
        $this->clearStorage();
        $this->getOrder();
        $this->canReorder(true);
        $this->createRedirect();
        $this->getOrderId(null);
        $this->setPath('sales/order/');

        $this->assertInstanceOf(Redirect::class, $this->reorder->execute());
    }

    public function testExecuteRedirectBack()
    {
        $this->clearStorage();
        $this->getOrder();
        $this->canReorder(true);
        $this->createRedirect();
        $this->getOrderId($this->orderId);
        $this->getUnavailableProducts([1, 3]);
        $this->messageManagerMock->expects($this->any())->method('addNoticeMessage');
        $this->setPath('sales/order/view', ['order_id' => $this->orderId]);

        $this->assertInstanceOf(Redirect::class, $this->reorder->execute());
    }

    public function testExecuteRedirectNewOrder()
    {
        $this->clearStorage();
        $this->getOrder();
        $this->canReorder(true);
        $this->createRedirect();
        $this->getOrderId($this->orderId);
        $this->getUnavailableProducts([]);
        $this->initFromOrder();
        $this->setPath('sales/*');

        $this->assertInstanceOf(Redirect::class, $this->reorder->execute());
    }

    private function clearStorage()
    {
        $this->objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(Quote::class)
            ->willReturn($this->quoteSessionMock);
        $this->quoteSessionMock->expects($this->once())->method('clearStorage')->will($this->returnSelf());
    }

    private function getOrder()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('order_id')
            ->willReturn($this->orderId);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($this->orderId)
            ->willReturn($this->orderMock);
    }

    /**
     * @param bool $result
     */
    private function canReorder($result)
    {
        $entity_id = 1;
        $this->orderMock->expects($this->once())->method('getEntityId')->willReturn($entity_id);
        $this->reorderHelperMock->expects($this->once())
            ->method('canReorder')
            ->with($entity_id)
            ->willReturn($result);
    }

    private function prepareForward()
    {
        $this->resultForwardFactoryMock->expects($this->once())->method('create')->willReturn($this->resultForwardMock);
        $this->resultForwardMock->expects($this->once())->method('forward')->with('noroute')->willReturnSelf();
    }

    private function createRedirect()
    {
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
    }

    /**
     * @param null|int $orderId
     */
    private function getOrderId($orderId)
    {
        $this->orderMock->expects($this->once())->method('getId')->willReturn($orderId);
    }

    /**
     * @param string $path
     * @param null|array $params
     */
    private function setPath($path, $params = [])
    {
        $this->resultRedirectMock->expects($this->once())->method('setPath')->with($path, $params);
    }

    /**
     * @param array $unavailableProducts
     */
    private function getUnavailableProducts(array $unavailableProducts)
    {
        $this->unavailableProductsProviderMock->expects($this->any())
            ->method('getForOrder')
            ->with($this->orderMock)
            ->willReturn($unavailableProducts);
    }

    private function initFromOrder()
    {
        $this->orderMock->expects($this->once())->method('setReordered')->with(true)->willReturnSelf();
        $this->objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(Quote::class)
            ->willReturn($this->quoteSessionMock);
        $this->quoteSessionMock->expects($this->once())
            ->method('setUseOldShippingMethod')
            ->with(true)
            ->will($this->returnSelf());
        $this->objectManagerMock->expects($this->at(2))
            ->method('get')
            ->with(Create::class)
            ->willReturn($this->orderCreateMock);
        $this->orderCreateMock->expects($this->once())
            ->method('initFromOrder')
            ->with($this->orderMock)
            ->willReturnSelf();
    }
}
