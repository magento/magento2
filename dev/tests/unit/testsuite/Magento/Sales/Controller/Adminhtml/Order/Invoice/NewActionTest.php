<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class NewActionTest
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 */
class NewActionTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
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

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $titleMock = $this->getMockBuilder('Magento\Framework\App\Action\Title')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->viewMock = $this->getMockBuilder('Magento\Backend\Model\View')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\ManagerInterface');
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $this->actionFlagMock = $this->getMockBuilder('Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->helperMock = $this->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->sessionMock = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['getCommentText', 'setIsUrlNotice'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($titleMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $contextMock->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($this->viewMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->will($this->returnValue($this->actionFlagMock));
        $contextMock->expects($this->any())
            ->method('getHelper')
            ->will($this->returnValue($this->helperMock));
        $contextMock->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManagerMock));
        $this->viewMock->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $this->controller = $objectManager->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Invoice\NewAction',
            ['context' => $contextMock]
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

        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('getTotalQty')
            ->willReturn(2);

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'canInvoice'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->with($orderId)
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);

        $orderService = $this->getMockBuilder('Magento\Sales\Model\Service\Order')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $orderService->expects($this->once())
            ->method('prepareInvoice')
            ->willReturn($invoiceMock);

        $menuBlockMock = $this->getMockBuilder('Magento\Backend\Block\Menu')
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

        $layoutMock = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('menu')
            ->will($this->returnValue($menuBlockMock));

        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));

        $this->sessionMock->expects($this->once())
            ->method('getCommentText')
            ->with(true)
            ->will($this->returnValue($commentText));

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Model\Order')
            ->willReturn($orderMock);
        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with('Magento\Sales\Model\Service\Order')
            ->willReturn($orderService);
        $this->objectManagerMock->expects($this->at(2))
            ->method('get')
            ->with('Magento\Backend\Model\Session')
            ->will($this->returnValue($this->sessionMock));

        $this->assertNull($this->controller->execute());
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

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'canInvoice'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->with($orderId)
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Model\Order')
            ->willReturn($orderMock);

        $this->assertNull($this->controller->execute());
    }
}
