<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class NewActionTest
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class NewActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitleMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\NewAction
     */
    protected $controller;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceServiceMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $titleMock = $this->getMockBuilder(\Magento\Framework\App\Action\Title::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->viewMock = $this->getMockBuilder(\Magento\Framework\App\View::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->actionFlagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->helperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCommentText', 'setIsUrlNotice'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\RedirectFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRequest',
                    'getResponse',
                    'getObjectManager',
                    'getTitle',
                    'getSession',
                    'getHelper',
                    'getActionFlag',
                    'getMessageManager',
                    'getResultRedirectFactory',
                    'getView'
                ]
            )
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($titleMock);
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

        $this->invoiceServiceMock = $this->getMockBuilder(\Magento\Sales\Model\Service\InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);

        $this->controller = $objectManager->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Invoice\NewAction::class,
            [
                'context' => $contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'invoiceService' => $this->invoiceServiceMock,
                'orderRepository' => $this->orderRepositoryMock
            ]
        );
    }

    public function testExecute()
    {
        $orderId = 1;
        $invoiceData = [];
        $commentText = 'comment test';

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue($orderId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('invoice', [])
            ->will($this->returnValue($invoiceData));

        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('getTotalQty')
            ->willReturn(2);

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'canInvoice'])
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

        $menuBlockMock = $this->getMockBuilder(\Magento\Backend\Block\Menu::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentItems', 'getMenuModel'])
            ->getMock();
        $menuBlockMock->expects($this->any())
            ->method('getMenuModel')
            ->will($this->returnSelf());
        $menuBlockMock->expects($this->any())
            ->method('getParentItems')
            ->with('Magento_Sales::sales_order')
            ->will($this->returnValue([]));

        $this->sessionMock->expects($this->once())
            ->method('getCommentText')
            ->with(true)
            ->will($this->returnValue($commentText));

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Backend\Model\Session::class)
            ->will($this->returnValue($this->sessionMock));

        $this->resultPageMock->expects($this->once())->method('setActiveMenu')->with('Magento_Sales::sales_order');
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->resultPageMock));

        $this->assertSame($this->resultPageMock, $this->controller->execute());
    }

    public function testExecuteNoOrder()
    {
        $orderId = 1;
        $invoiceData = [];

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue($orderId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('invoice', [])
            ->will($this->returnValue($invoiceData));

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['canInvoice'])
            ->getMock();

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($orderMock);

        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('sales/order/view', ['order_id' => $orderId]);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultRedirect));

        $this->assertSame($resultRedirect, $this->controller->execute());
    }
}
