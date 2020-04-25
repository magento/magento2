<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $this->creditmemoLoaderMock = $this->createPartialMock(
            CreditmemoLoader::class,
            ['setOrderId', 'setCreditmemoId', 'setCreditmemo', 'setInvoiceId', 'load']
        );
        $this->creditmemoMock = $this->createPartialMock(
            Creditmemo::class,
            ['getInvoice', '__wakeup', 'setCommentText']
        );
        $this->invoiceMock = $this->createPartialMock(
            Invoice::class,
            ['getIncrementId', '__wakeup']
        );
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
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
        $this->backendSessionMock = $this->createPartialMock(Session::class, ['getCommentText']);
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
            ->setMethods(['create'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->contextMock->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $this->contextMock->expects($this->once())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));

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
            ->will($this->returnValueMap([
                ['order_id', null, 'order_id'],
                ['creditmemo_id', null, 'creditmemo_id'],
                ['creditmemo', null, 'creditmemo'],
                ['invoice_id', null, 'invoice_id'],
            ]));
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setOrderId')
            ->with($this->equalTo('order_id'));
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setCreditmemoId')
            ->with($this->equalTo('creditmemo_id'));
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setCreditmemo')
            ->with($this->equalTo('creditmemo'));
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setInvoiceId')
            ->with($this->equalTo('invoice_id'));
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->creditmemoMock));
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('getInvoice')
            ->will($this->returnValue($this->invoiceMock));
        $this->invoiceMock->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue('invoice-increment-id'));
        $this->titleMock->expects($this->exactly(2))
            ->method('prepend')
            ->will($this->returnValueMap([
                ['Credit Memos', null],
                ['New Memo for #invoice-increment-id', null],
                ['item-title', null],
            ]));
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo(Session::class))
            ->will($this->returnValue($this->backendSessionMock));
        $this->backendSessionMock->expects($this->once())
            ->method('getCommentText')
            ->with($this->equalTo(true))
            ->will($this->returnValue('comment'));
        $this->creditmemoMock->expects($this->once())
            ->method('setCommentText')
            ->with($this->equalTo('comment'));
        $this->resultPageMock->expects($this->any())->method('getConfig')->will(
            $this->returnValue($this->pageConfigMock)
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
