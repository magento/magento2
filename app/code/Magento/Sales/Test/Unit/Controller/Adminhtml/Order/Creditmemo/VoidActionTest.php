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
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\AddComment;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\VoidAction;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class VoidActionTest extends TestCase
{
    /**
     * @var AddComment
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
    protected $senderMock;

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
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $actionFlagMock;

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
    protected $helperMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var Forward|MockObject
     */
    protected $resultForwardMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->addMethods(['cancel', 'void'])
            ->onlyMethods(['getInvoice', 'getOrder', 'getId'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods(
                [
                    'getRequest',
                    'getResponse',
                    'getObjectManager',
                    'getSession',
                    'getHelper',
                    'getActionFlag',
                    'getMessageManager',
                    'getResultRedirectFactory'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->loaderMock = $this->getMockBuilder(CreditmemoLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->senderMock = $this->getMockBuilder(CreditmemoSender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            ForwardFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);
        $this->contextMock->expects($this->any())
            ->method('getHelper')
            ->willReturn($this->helperMock);
        $this->contextMock->expects($this->any())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $objectManager = new ObjectManager($this);
        $this->controller = $objectManager->getObject(
            VoidAction::class,
            [
                'context' => $this->contextMock,
                'creditmemoLoader' => $this->loaderMock,
                'resultForwardFactory' => $this->resultForwardFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteNoCreditmemo()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->withAnyParameters()
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
     * @return void
     */
    public function testExecuteModelException()
    {
        $id = 123;
        $message = 'Model exception';
        $e = new LocalizedException(__($message));

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->withAnyParameters()
            ->willReturnArgument(0);
        $this->creditmemoMock->expects($this->once())
            ->method('void')
            ->willThrowException($e);
        $this->loaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->creditmemoMock);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->creditmemoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($id);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/view', ['creditmemo_id' => $id])
            ->willReturnSelf();

        $this->assertInstanceOf(
            Redirect::class,
            $this->controller->execute()
        );
    }

    /**
     * @return void
     */
    public function testExecuteException()
    {
        $id = 321;
        $message = 'Model exception';
        $e = new \Exception($message);

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->withAnyParameters()
            ->willReturnArgument(0);
        $this->creditmemoMock->expects($this->once())
            ->method('void')
            ->willThrowException($e);
        $this->loaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->creditmemoMock);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->creditmemoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($id);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/view', ['creditmemo_id' => $id])
            ->willReturnSelf();

        $this->assertInstanceOf(
            Redirect::class,
            $this->controller->execute()
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $id = '111';

        $transactionMock = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->withAnyParameters()
            ->willReturnArgument(0);
        $this->loaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->creditmemoMock);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Transaction::class)
            ->willReturn($transactionMock);
        $this->creditmemoMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($orderMock);
        $this->creditmemoMock->expects($this->any())
            ->method('getInvoice')
            ->willReturn($invoiceMock);
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You voided the credit memo.');
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->creditmemoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($id);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/view', ['creditmemo_id' => $id])
            ->willReturnSelf();

        $this->assertInstanceOf(
            Redirect::class,
            $this->controller->execute()
        );
    }
}
