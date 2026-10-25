<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Menu;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\View;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\NewAction;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class NewActionTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $responseMock;

    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var MockObject
     */
    protected $actionFlagMock;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $viewMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    protected $pageTitleMock;

    /**
     * @var NewAction
     */
    protected $controller;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var InvoiceService|MockObject
     */
    protected $invoiceServiceMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepositoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->viewMock = $this->createMock(View::class);

        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $this->actionFlagMock = $this->createMock(ActionFlag::class);

        $this->helperMock = $this->createMock(Data::class);

        $this->requestMock = $this->createMock(Http::class);
        $this->responseMock = $this->createMock(ResponseHttp::class);

        $this->sessionMock = $this->createPartialMockWithReflection(
            Session::class,
            ['getCommentText', 'setIsUrlNotice']
        );
        $this->resultPageMock = $this->createMock(Page::class);
        $this->pageConfigMock = $this->createMock(Config::class);
        $this->pageTitleMock = $this->createMock(Title::class);
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getRequest',
                'getResponse',
                'getObjectManager',
                'getSession',
                'getHelper',
                'getActionFlag',
                'getMessageManager',
                'getResultRedirectFactory',
                'getView'
            ])
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $contextMock->expects($this->any())
            ->method('getView')
            ->willReturn($this->viewMock);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);
        $contextMock->expects($this->any())
            ->method('getHelper')
            ->willReturn($this->helperMock);
        $contextMock->expects($this->any())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->viewMock->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $this->invoiceServiceMock = $this->createMock(InvoiceService::class);

        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);

        $this->controller = $objectManager->getObject(
            NewAction::class,
            [
                'context' => $contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'invoiceService' => $this->invoiceServiceMock,
                'orderRepository' => $this->orderRepositoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $orderId = 1;
        $invoiceData = [];
        $commentText = 'comment test';

        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(function ($arg1, $arg2) use ($orderId, $invoiceData) {
                if ($arg1 == 'order_id' && empty($arg2)) {
                    return $orderId;
                } elseif ($arg1 == 'invoice' && empty($arg2)) {
                    return $invoiceData;
                }
            });

        $invoiceMock = $this->createMock(Invoice::class);
        $invoiceMock->expects($this->once())
            ->method('getTotalQty')
            ->willReturn(2);

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'canInvoice'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($orderMock);

        $this->invoiceServiceMock->expects($this->once())
            ->method('prepareInvoice')
            ->with($orderMock, [])
            ->willReturn($invoiceMock);

        $menuBlockMock = $this->createPartialMockWithReflection(
            Menu::class,
            ['getMenuModel', 'getParentItems']
        );
        $menuBlockMock->expects($this->any())
            ->method('getMenuModel')->willReturnSelf();
        $menuBlockMock->expects($this->any())
            ->method('getParentItems')
            ->with('Magento_Sales::sales_order')
            ->willReturn([]);

        $this->sessionMock->expects($this->once())
            ->method('getCommentText')
            ->with(true)
            ->willReturn($commentText);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Session::class)
            ->willReturn($this->sessionMock);

        $this->resultPageMock->expects($this->once())->method('setActiveMenu')->with('Magento_Sales::sales_order');
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $this->assertSame($this->resultPageMock, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteNoOrder(): void
    {
        $orderId = 1;
        $invoiceData = [];

        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(function ($arg1, $arg2) use ($orderId, $invoiceData) {
                if ($arg1 == 'order_id' && empty($arg2)) {
                    return $orderId;
                } elseif ($arg1 == 'invoice' && empty($arg2)) {
                    return $invoiceData;
                }
            });

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['canInvoice'])
            ->getMock();

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($orderMock);

        $resultRedirect = $this->createMock(Redirect::class);
        $resultRedirect->expects($this->once())->method('setPath')->with('sales/order/view', ['order_id' => $orderId]);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirect);

        $this->assertSame($resultRedirect, $this->controller->execute());
    }
}
