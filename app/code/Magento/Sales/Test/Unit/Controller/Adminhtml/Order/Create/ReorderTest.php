<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Create\Reorder;
use Magento\Sales\Helper\Reorder as ReorderHelper;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Reorder\UnavailableProductsProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Verify reorder class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ReorderTest extends TestCase
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
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var ForwardFactory|MockObject
     */
    private $resultForwardFactoryMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Forward|MockObject
     */
    private $resultForwardMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteSessionMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var ReorderHelper|MockObject
     */
    private $reorderHelperMock;

    /**
     * @var UnavailableProductsProvider|MockObject
     */
    private $unavailableProductsProviderMock;

    /**
     * @var Create|MockObject
     */
    private $orderCreateMock;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->orderId = 111;
        $this->orderRepositoryMock = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->resultForwardFactoryMock = $this->createMock(ForwardFactory::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->resultForwardMock = $this->createMock(Forward::class);
        $this->reorderHelperMock = $this->createMock(ReorderHelper::class);
        $this->unavailableProductsProviderMock = $this->createMock(UnavailableProductsProvider::class);
        $this->orderCreateMock = $this->createMock(Create::class);
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId', 'getId', 'setReordered'])
            ->getMock();
        $this->quoteSessionMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['clearStorage', 'setUseOldShippingMethod'])
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $objectManager = new ObjectManager($this);
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
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Verify execute with no route.
     *
     * @return void
     */
    public function testExecuteForward(): void
    {
        $this->clearStorage();
        $this->getOrder();
        $this->canReorder(false);
        $this->prepareForward();

        $this->assertInstanceOf(Forward::class, $this->reorder->execute());
    }

    /**
     * Verify execute redirect order grid
     *
     * @return void
     */
    public function testExecuteRedirectOrderGrid(): void
    {
        $this->clearStorage();
        $this->getOrder();
        $this->canReorder(true);
        $this->createRedirect();
        $this->getOrderId(null);
        $this->setPath('sales/order/');

        $this->assertInstanceOf(Redirect::class, $this->reorder->execute());
    }

    /**
     * Verify execute redirect back.
     *
     * @return void
     */
    public function testExecuteRedirectBack(): void
    {
        $this->clearStorage();
        $this->getOrder();
        $this->canReorder(true);
        $this->createRedirect();
        $this->getOrderId($this->orderId);
        $this->getUnavailableProducts([1, 3]);
        $this->messageManagerMock->expects($this->any())->method('addErrorMessage');
        $this->setPath('sales/order/view', ['order_id' => $this->orderId]);

        $this->assertInstanceOf(Redirect::class, $this->reorder->execute());
    }

    /**
     * Verify execute redirect new order.
     *
     * @return void
     */
    public function testExecuteRedirectNewOrder(): void
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

    /**
     * Verify redirect new order with throws exception.
     *
     * @return void
     */
    public function testExecuteRedirectNewOrderWithThrowsException(): void
    {
        $exception = new NoSuchEntityException();

        $this->clearStorage();
        $this->getOrder();
        $this->canReorder(true);
        $this->createRedirect();
        $this->getOrderId($this->orderId);
        $this->getUnavailableProducts([]);

        $this->orderMock->expects($this->once())
            ->method('setReordered')
            ->with(true)
            ->willThrowException($exception);
        $this->messageManagerMock
            ->expects($this->once())
            ->method('addErrorMessage')
            ->willReturnSelf();
        $this->resultRedirectMock
            ->expects($this->once())
            ->method('setPath')
            ->with('sales/*')
            ->willReturnSelf();
        $this->assertInstanceOf(Redirect::class, $this->reorder->execute());
    }

    /**
     * Verify redirect new order with exception.
     *
     * @return void
     */
    public function testExecuteRedirectNewOrderWithException(): void
    {
        $exception = new \Exception();

        $this->clearStorage();
        $this->getOrder();
        $this->canReorder(true);
        $this->createRedirect();
        $this->getOrderId($this->orderId);
        $this->getUnavailableProducts([]);
        $this->orderMock->expects($this->once())
            ->method('setReordered')
            ->with(true)
            ->willThrowException(new $exception());
        $this->messageManagerMock
            ->expects($this->once())
            ->method('addException')
            ->with($exception, __('Error while processing order.'))
            ->willReturnSelf();
        $this->resultRedirectMock
            ->expects($this->once())
            ->method('setPath')
            ->with('sales/*')
            ->willReturnSelf();
        $this->assertInstanceOf(Redirect::class, $this->reorder->execute());
    }

    /**
     * Verify redirect new order with throws out of stock exception.
     *
     * @return void
     */
    public function testExecuteReorderWithThrowsLocalizedException(): void
    {
        $errorPhrase = __('This product is out of stock.');
        $exception = new LocalizedException($errorPhrase);

        $this->clearStorage();
        $this->getOrder();
        $this->canReorder(true);
        $this->createRedirect();
        $this->getOrderId($this->orderId);
        $this->getUnavailableProducts([]);

        $this->orderMock->expects($this->once())
            ->method('setReordered')
            ->with(true)
            ->willThrowException($exception);
        $this->loggerMock
            ->expects($this->any())
            ->method('critical')
            ->willReturn($exception);
        $this->messageManagerMock
            ->expects($this->once())
            ->method('addErrorMessage')
            ->willReturnSelf();
        $this->resultRedirectMock
            ->expects($this->once())
            ->method('setPath')
            ->with('sales/*')
            ->willReturnSelf();
        $this->assertInstanceOf(Redirect::class, $this->reorder->execute());
    }

    /**
     * Mock clear storage.
     *
     * @return void
     */
    private function clearStorage(): void
    {
        $this->objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(Quote::class)
            ->willReturn($this->quoteSessionMock);
        $this->quoteSessionMock->expects($this->once())->method('clearStorage')->willReturnSelf();
    }

    /**
     * Mock get order.
     *
     * @return void
     */
    private function getOrder(): void
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
     * Mock and return 'canReorder' method.
     *
     * @param bool $result
     * @return void
     */
    private function canReorder(bool $result): void
    {
        $entityId = 1;
        $this->orderMock->expects($this->once())->method('getEntityId')->willReturn($entityId);
        $this->reorderHelperMock->expects($this->once())
            ->method('canReorder')
            ->with($entityId)
            ->willReturn($result);
    }

    /**
     * Mock result forward.
     *
     * @return void
     */
    private function prepareForward(): void
    {
        $this->resultForwardFactoryMock->expects($this->once())->method('create')->willReturn($this->resultForwardMock);
        $this->resultForwardMock->expects($this->once())->method('forward')->with('noroute')->willReturnSelf();
    }

    /**
     * Mock create.
     *
     * @return void
     */
    private function createRedirect(): void
    {
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
    }

    /**
     * Mock order 'getId' method.
     *
     * @param null|int $orderId
     * @return void
     */
    private function getOrderId($orderId): void
    {
        $this->orderMock->expects($this->once())->method('getId')->willReturn($orderId);
    }

    /**
     * Mock result redirect 'setPath' method.
     *
     * @param string $path
     * @param null|array $params
     * @return void
     */
    private function setPath(string $path, $params = []): void
    {
        $this->resultRedirectMock->expects($this->once())->method('setPath')->with($path, $params);
    }

    /**
     * Mock unavailable products provider.
     *
     * @param array $unavailableProducts
     * @return void
     */
    private function getUnavailableProducts(array $unavailableProducts): void
    {
        $this->unavailableProductsProviderMock->expects($this->any())
            ->method('getForOrder')
            ->with($this->orderMock)
            ->willReturn($unavailableProducts);
    }

    /**
     * Mock init form order.
     *
     * @return void
     */
    private function initFromOrder(): void
    {
        $this->orderMock->expects($this->once())->method('setReordered')->with(true)->willReturnSelf();
        $this->objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(Quote::class)
            ->willReturn($this->quoteSessionMock);
        $this->quoteSessionMock->expects($this->once())
            ->method('setUseOldShippingMethod')
            ->with(true)->willReturnSelf();
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
