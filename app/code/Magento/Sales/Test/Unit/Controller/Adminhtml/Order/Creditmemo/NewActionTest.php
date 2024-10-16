<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\NewAction;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewActionTest extends TestCase
{
    /**
     * @var NewAction
     */
    protected $controller;

    /**
     * @var MockObject|Context
     */
    protected $contextMock;

    /**
     * @var MockObject|CreditmemoLoader
     */
    protected $creditmemoLoaderMock;

    /**
     * @var MockObject|RequestInterface
     */
    protected $requestMock;

    /**
     * @var MockObject|ResponseInterface
     */
    protected $responseMock;

    /**
     * @var MockObject|Creditmemo
     */
    protected $creditmemoMock;

    /**
     * @var MockObject|Invoice
     */
    protected $invoiceMock;

    /**
     * @var MockObject
     */
    protected $pageConfigMock;

    /**
     * @var MockObject|Title
     */
    protected $titleMock;

    /**
     * @var MockObject|ObjectManagerInterface
     */
    protected $objectManagerMock;

    /**
     * @var MockObject|Session
     */
    protected $backendSessionMock;

    /**
     * @var MockObject|LayoutInterface
     */
    protected $layoutMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->creditmemoLoaderMock = $this->getMockBuilder(CreditmemoLoader::class)
            ->addMethods(['setOrderId', 'setCreditmemoId', 'setCreditmemo', 'setInvoiceId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->addMethods(['setCommentText'])
            ->onlyMethods(['getInvoice'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceMock = $this->createPartialMock(
            Invoice::class,
            ['getIncrementId']
        );
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->responseMock = $this->getMockForAbstractClass(
            ResponseInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->titleMock = $this->createMock(Title::class);
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getCommentText'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockForAbstractClass(
            LayoutInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);

        $objectManager = new ObjectManager($this);
        $this->controller = $objectManager->getObject(
            NewAction::class,
            [
                'context' => $this->contextMock,
                'creditmemoLoader' => $this->creditmemoLoaderMock,
                'resultPageFactory' => $this->resultPageFactoryMock
            ]
        );
    }

    /**
     *  test execute method
     */
    public function testExecute()
    {
        $this->requestMock->expects($this->exactly(4))
            ->method('getParam')
            ->willReturnMap([
                ['order_id', null, 'order_id'],
                ['creditmemo_id', null, 'creditmemo_id'],
                ['creditmemo', null, 'creditmemo'],
                ['invoice_id', null, 'invoice_id'],
            ]);
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setOrderId')
            ->with('order_id');
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setCreditmemoId')
            ->with('creditmemo_id');
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setCreditmemo')
            ->with('creditmemo');
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setInvoiceId')
            ->with('invoice_id');
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->creditmemoMock);
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('getInvoice')
            ->willReturn($this->invoiceMock);
        $this->invoiceMock->expects($this->once())
            ->method('getIncrementId')
            ->willReturn('invoice-increment-id');
        $this->titleMock->expects($this->exactly(2))
            ->method('prepend')
            ->willReturnMap([
                ['Credit Memos', null],
                ['New Memo for #invoice-increment-id', null],
                ['item-title', null],
            ]);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Session::class)
            ->willReturn($this->backendSessionMock);
        $this->backendSessionMock->expects($this->once())
            ->method('getCommentText')
            ->with(true)
            ->willReturn('comment');
        $this->creditmemoMock->expects($this->once())
            ->method('setCommentText')
            ->with('comment');
        $this->resultPageMock->expects($this->any())->method('getConfig')->willReturn(
            $this->pageConfigMock
        );
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->titleMock);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Sales::sales_order')
            ->willReturnSelf();
        $this->resultPageMock->expects($this->atLeastOnce())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);

        $this->assertInstanceOf(
            Page::class,
            $this->controller->execute()
        );
    }
}
