<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Block\Order\Items;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\UpdateQty;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateQtyTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $viewMock;

    /**
     * @var MockObject
     */
    protected $resultPageMock;

    /**
     * @var MockObject
     */
    protected $pageConfigMock;

    /**
     * @var MockObject
     */
    protected $objectManagerMock;

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
    protected $titleMock;

    /**
     * @var UpdateQty
     */
    protected $controller;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var RawFactory|MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var JsonFactory|MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var InvoiceService|MockObject
     */
    protected $invoiceServiceMock;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->titleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewMock = $this->getMockBuilder(ViewInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->viewMock->expects($this->any())->method('loadLayout')->willReturnSelf();

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->pageConfigMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);

        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getRequest',
                    'getResponse',
                    'getObjectManager',
                    'getSession',
                    'getHelper',
                    'getActionFlag',
                    'getMessageManager',
                    'getResultRedirectFactory',
                    'getView'
                ]
            )->addMethods(['getTitle'])
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->titleMock);
        $contextMock->expects($this->any())
            ->method('getView')
            ->willReturn($this->viewMock);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);

        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultRawFactoryMock = $this->getMockBuilder(RawFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->invoiceServiceMock = $this->getMockBuilder(InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            UpdateQty::class,
            [
                'context' => $contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultRawFactory' => $this->resultRawFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'invoiceService' => $this->invoiceServiceMock
            ]
        );
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecute(): void
    {
        $orderId = 1;
        $invoiceData = ['comment_text' => 'test'];
        $response = 'test data';

        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['order_id'], ['invoice', []])
            ->willReturnOnConsecutiveCalls($orderId, $invoiceData);

        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('getTotalQty')
            ->willReturn(2);

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'canInvoice'])
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

        $this->invoiceServiceMock->expects($this->once())
            ->method('prepareInvoice')
            ->with($orderMock, [])
            ->willReturn($invoiceMock);

        $this->objectManagerMock
            ->method('create')
            ->with(Order::class)
            ->willReturn($orderMock);

        $blockItemMock = $this->getMockBuilder(Items::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockItemMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($response);

        $layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('order_items')
            ->willReturn($blockItemMock);

        $this->resultPageMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);
        $this->resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);

        $this->pageConfigMock->expects($this->once())->method('getTitle')->willReturn($this->titleMock);

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $resultRaw = $this->getMockBuilder(Raw::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRaw->expects($this->once())->method('setContents')->with($response);

        $this->resultRawFactoryMock->expects($this->once())->method('create')->willReturn($resultRaw);

        $this->assertSame($resultRaw, $this->controller->execute());
    }

    /**
     * Test execute model exception
     *
     * @return void
     */
    public function testExecuteModelException(): void
    {
        $message = 'The order no longer exists.';
        $response = ['error' => true, 'message' => $message];

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'canInvoice'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->objectManagerMock
            ->method('create')
            ->with(Order::class)
            ->willReturn($orderMock);

        $this->titleMock->expects($this->never())
            ->method('prepend')
            ->with('Invoices');

        /** @var Json|MockObject */
        $resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonMock->expects($this->once())->method('setData')->with($response);

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }

    /**
     * Test execute exception
     *
     * @return void
     */
    public function testExecuteException(): void
    {
        $message = 'The order no longer exists.';
        $response = ['error' => true, 'message' => $message];

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'canInvoice'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->objectManagerMock
            ->method('create')
            ->with(Order::class)
            ->willReturn($orderMock);

        $this->titleMock->expects($this->never())
            ->method('prepend')
            ->with('Invoices');

        /** @var Json|MockObject */
        $resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonMock->expects($this->once())->method('setData')->with($response);

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }
}
