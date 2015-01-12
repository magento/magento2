<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class UpdateQtyTest
 *
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 */
class UpdateQtyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

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
    protected $titleMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\UpdateQty
     */
    protected $controller;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->titleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->setMethods(['getConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewMock = $this->getMockBuilder('Magento\Framework\App\ViewInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewMock->expects($this->any())->method('loadLayout')->will($this->returnSelf());

        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $this->pageConfigMock->expects($this->any())->method('getTitle')->will($this->returnValue($this->titleMock));

        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($this->titleMock));
        $contextMock->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($this->viewMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));

        $this->controller = $objectManager->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Invoice\UpdateQty',
            [
                'context' => $contextMock
            ]
        );
    }

    public function testExecute()
    {
        $orderId = 1;
        $invoiceData = ['comment_text' => 'test'];
        $response = 'test data';

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

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Model\Order')
            ->willReturn($orderMock);
        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with('Magento\Sales\Model\Service\Order')
            ->willReturn($orderService);

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($response);

        $blockItemMock = $this->getMockBuilder('Magento\Sales\Block\Order\Items')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $blockItemMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($response));

        $layoutMock = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('order_items')
            ->will($this->returnValue($blockItemMock));
        $this->viewMock->expects($this->once())->method('getPage')->will($this->returnValue($this->resultPageMock));
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));
        $this->resultPageMock->expects($this->once())->method('getConfig')->will(
            $this->returnValue($this->pageConfigMock)
        );
        $this->pageConfigMock->expects($this->once())->method('getTitle')->will($this->returnValue($this->titleMock));

        $this->assertNull($this->controller->execute());
    }

    public function testExecuteModelException()
    {
        $message = 'Cannot update item quantity.';
        $response = ['error' => true, 'message' => $message];

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'canInvoice'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Model\Order')
            ->willReturn($orderMock);

        $this->titleMock->expects($this->never())
            ->method('prepend')
            ->with('Invoices');

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with(json_encode($response));

        $helperMock = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $helperMock->expects($this->once())
            ->method('jsonEncode')
            ->with($response)
            ->will($this->returnValue(json_encode($response)));

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Core\Helper\Data')
            ->will($this->returnValue($helperMock));

        $this->assertNull($this->controller->execute());
    }

    public function testExecuteException()
    {
        $message = 'Cannot update item quantity.';
        $response = ['error' => true, 'message' => $message];

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'canInvoice'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Model\Order')
            ->willReturn($orderMock);
        $this->titleMock->expects($this->never())
            ->method('prepend')
            ->with('Invoices');

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with(json_encode($response));

        $helperMock = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $helperMock->expects($this->once())
            ->method('jsonEncode')
            ->with($response)
            ->will($this->returnValue(json_encode($response)));

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Core\Helper\Data')
            ->will($this->returnValue($helperMock));

        $this->assertNull($this->controller->execute());
    }
}
