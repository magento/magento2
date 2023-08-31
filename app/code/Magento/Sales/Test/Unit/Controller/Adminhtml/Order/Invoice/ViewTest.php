<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Menu;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\View;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ViewTest extends TestCase
{
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
     * @var MockObject
     */
    protected $viewMock;

    /**
     * @var MockObject
     */
    protected $actionFlagMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $invoiceLoaderMock;

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
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var View
     */
    protected $controller;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var InvoiceRepositoryInterface|MockObject
     */
    protected $invoiceRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(\Magento\Framework\App\View::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCommentText', 'setIsUrlNotice'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            ->method('getView')
            ->willReturn($this->viewMock);
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);
        $contextMock->expects($this->any())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->viewMock->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceRepository = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            View::class,
            [
                'context' => $contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultForwardFactory' => $this->resultForwardFactoryMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $this->controller,
            'invoiceRepository',
            $this->invoiceRepository
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $invoiceId = 2;

        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['invoice_id'], ['come_from'])
            ->willReturnOnConsecutiveCalls($invoiceId, 'anything');

        $menuBlockMock = $this->getMockBuilder(Menu::class)->disableOriginalConstructor()
            ->onlyMethods(['getMenuModel'])
            ->addMethods(['getParentItems'])
            ->getMock();
        $menuBlockMock->expects($this->any())
            ->method('getMenuModel')->willReturnSelf();
        $menuBlockMock->expects($this->any())
            ->method('getParentItems')
            ->with('Magento_Sales::sales_order')
            ->willReturn([]);

        $invoiceViewBlockMock = $this->getMockBuilder(\Magento\Sales\Block\Adminhtml\Order\Invoice\View::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateBackButtonUrl'])
            ->getMock();

        $layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->method('getBlock')
            ->with('sales_invoice_view')
            ->willReturn($invoiceViewBlockMock);

        $this->resultPageMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->resultPageMock->expects($this->once())->method('setActiveMenu')->with('Magento_Sales::sales_order');

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $this->assertSame($this->resultPageMock, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteNoInvoice(): void
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->prepareRedirect();
        $this->setPath('sales/invoice');
        $this->assertInstanceOf(
            Redirect::class,
            $this->controller->execute()
        );
    }

    /**
     * prepareRedirect
     *
     * @return void
     */
    protected function prepareRedirect(): void
    {
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
    }

    /**
     * @param string $path
     * @param array $params
     *
     * @return void
     */
    protected function setPath(string $path, array $params = []): void
    {
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with($path, $params);
    }
}
