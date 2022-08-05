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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Cancel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CancelTest extends TestCase
{
    /**
     * @var Cancel
     */
    protected $controller;

    /**
     * @var MockObject
     */
    protected $contextMock;

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
     * @var CreditmemoManagementInterface|MockObject
     */
    protected $creditmemoManagementMock;

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
        $this->creditmemoManagementMock = $this->getMockForAbstractClass(CreditmemoManagementInterface::class);
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
        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            ForwardFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(
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
        $this->contextMock->expects($this->any())
            ->method('getHelper')
            ->willReturn($this->helperMock);
        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
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
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $objectManager = new ObjectManager($this);
        $this->controller = $objectManager->getObject(
            Cancel::class,
            [
                'context' => $this->contextMock,
                'resultForwardFactory' => $this->resultForwardFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteModelException()
    {
        $creditmemoId = 123;
        $message = 'Model exception';
        $e = new LocalizedException(__($message));

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('creditmemo_id')
            ->willReturn($creditmemoId);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(CreditmemoManagementInterface::class)
            ->willReturn($this->creditmemoManagementMock);
        $this->creditmemoManagementMock->expects($this->once())
            ->method('cancel')
            ->with($creditmemoId)
            ->willThrowException($e);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/view', ['creditmemo_id' => $creditmemoId])
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
        $creditmemoId = 321;
        $message = 'Model exception';
        $e = new \Exception($message);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('creditmemo_id')
            ->willReturn($creditmemoId);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(CreditmemoManagementInterface::class)
            ->willReturn($this->creditmemoManagementMock);
        $this->creditmemoManagementMock->expects($this->once())
            ->method('cancel')
            ->with($creditmemoId)
            ->willThrowException($e);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/view', ['creditmemo_id' => $creditmemoId])
            ->willReturnSelf();

        $this->assertInstanceOf(
            Redirect::class,
            $this->controller->execute()
        );
    }

    /**
     * @return void
     */
    public function testExecuteNoCreditmemo()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('creditmemo_id')
            ->willReturn(null);
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
    public function testExecute()
    {
        $creditmemoId = '111';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('creditmemo_id')
            ->willReturn($creditmemoId);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(CreditmemoManagementInterface::class)
            ->willReturn($this->creditmemoManagementMock);
        $this->creditmemoManagementMock->expects($this->once())
            ->method('cancel')
            ->with($creditmemoId);
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The credit memo has been canceled.');
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/view', ['creditmemo_id' => $creditmemoId])
            ->willReturnSelf();

        $this->assertInstanceOf(
            Redirect::class,
            $this->controller->execute()
        );
    }
}
