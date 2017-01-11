<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

/**
 * Class NewActionTest
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\NewAction
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\App\Action\Context
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResponseInterface
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order\Creditmemo
     */
    protected $creditmemoMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order\Invoice
     */
    protected $invoiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Page\Title
     */
    protected $titleMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Session
     */
    protected $backendSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\LayoutInterface
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMock(\Magento\Backend\App\Action\Context::class, [], [], '', false);
        $this->creditmemoLoaderMock = $this->getMock(
            \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader::class,
            ['setOrderId', 'setCreditmemoId', 'setCreditmemo', 'setInvoiceId', 'load'],
            [],
            '',
            false
        );
        $this->creditmemoMock = $this->getMock(
            \Magento\Sales\Model\Order\Creditmemo::class,
            ['getInvoice', '__wakeup', 'setCommentText'],
            [],
            '',
            false
        );
        $this->invoiceMock = $this->getMock(
            \Magento\Sales\Model\Order\Invoice::class,
            ['getIncrementId', '__wakeup'],
            [],
            '',
            false
        );
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->responseMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\ResponseInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->titleMock = $this->getMock(\Magento\Framework\View\Page\Title::class, [], [], '', false);
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendSessionMock = $this->getMock(
            \Magento\Backend\Model\Session::class,
            ['getCommentText'],
            [],
            '',
            false
        );
        $this->layoutMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\LayoutInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Page::class)
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

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\NewAction::class,
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
            ->with($this->equalTo(\Magento\Backend\Model\Session::class))
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
            \Magento\Backend\Model\View\Result\Page::class,
            $this->controller->execute()
        );
    }
}
