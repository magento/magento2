<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\View as ViewCreditmemo;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\View;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends TestCase
{
    /**
     * @var View
     */
    protected $controller;

    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var MockObject
     */
    protected $loaderMock;

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
    protected $creditmemoMock;

    /**
     * @var MockObject
     */
    protected $messageManagerMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

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
    protected $invoiceMock;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    protected $pageTitleMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var Forward|MockObject
     */
    protected $resultForwardMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $redirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    private $redirectResultMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->invoiceMock = $this->createMock(Invoice::class);
        $this->creditmemoMock = $this->createMock(Creditmemo::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->responseMock = $this->createMock(HttpResponse::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->messageManagerMock = $this->createMock(Manager::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->actionFlagMock = $this->createMock(ActionFlag::class);
        $this->loaderMock = $this->createMock(CreditmemoLoader::class);
        $this->pageConfigMock = $this->createMock(Config::class);
        $this->pageTitleMock = $this->createMock(Title::class);
        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->resultPageMock = $this->createMock(Page::class);
        $this->resultForwardFactoryMock = $this->createMock(ForwardFactory::class);
        $this->resultForwardMock = $this->createMock(Forward::class);
        $this->redirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->redirectResultMock = $this->createMock(Redirect::class);
        $this->contextMock = $this->getMockBuilder(Context::class)
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
                    'getResultRedirectFactory'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $titleMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getHelper')
            ->willReturn($this->helperMock);
        $this->contextMock->expects($this->any())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($titleMock);
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $objectManager = new ObjectManager($this);
        $this->controller = $objectManager->getObject(
            View::class,
            [
                'context' => $this->contextMock,
                'creditmemoLoader' => $this->loaderMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultForwardFactory' => $this->resultForwardFactoryMock,
                'resultRedirectFactory'=> $this->redirectFactoryMock,
                'resultRedirect' => $this->redirectResultMock
            ]
        );
    }

    public function testExecuteNoCreditMemo()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnArgument(0);
        $this->loaderMock->expects($this->once())
            ->method('load')
            ->willThrowException(
                new NoSuchEntityException(
                    __('This creditmemo no longer exists.')
                )
            );
        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectResultMock);
        $this->redirectResultMock->expects($this->once())
            ->method('setPath')
            ->with('sales/creditmemo')
            ->willReturnSelf();

        $this->assertInstanceOf(
            RedirectResult::class,
            $this->controller->execute()
        );
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute($invoice)
    {
        $layoutMock = $this->createMock(Layout::class);
        $blockMock = $this->createMock(ViewCreditmemo::class);

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->withAnyParameters()
            ->willReturnArgument(0);
        $this->loaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->creditmemoMock);
        $this->creditmemoMock->expects($this->any())
            ->method('getInvoice')
            ->willReturn($invoice);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('sales_creditmemo_view')
            ->willReturn($blockMock);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->atLeastOnce())
            ->method('getLayout')
            ->willReturn($layoutMock);
        $this->resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Sales::sales_creditmemo')
            ->willReturnSelf();
        $this->resultPageMock->expects($this->atLeastOnce())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);

        $this->assertInstanceOf(
            Page::class,
            $this->controller->execute()
        );
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [false],
            [$this->invoiceMock]
        ];
    }
}
