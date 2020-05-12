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
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $titleMock = $this->getMockBuilder(\Magento\Framework\App\Action\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInvoice', 'getOrder', 'cancel', 'getId'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->messageManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->contextMock->expects($this->any())
            ->method('getHelper')
            ->willReturn($this->helperMock);
        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loaderMock = $this->getMockBuilder(CreditmemoLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            ForwardFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();

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
                'resultForwardFactory' => $this->resultForwardFactoryMock
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
            ->willReturn(false);
        $this->resultForwardFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultForwardMock);
        $this->resultForwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertInstanceOf(
            Forward::class,
            $this->controller->execute()
        );
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute($invoice)
    {
        $layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockMock = $this->getMockBuilder(\Magento\Sales\Block\Adminhtml\Order\Creditmemo\View::class)
            ->disableOriginalConstructor()
            ->getMock();

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
