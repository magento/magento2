<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

/**
 * Class CancelTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CancelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Cancel
     */
    protected $controller;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Sales\Api\CreditmemoManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $creditmemoManagementMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultForwardMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->creditmemoManagementMock = $this->createMock(\Magento\Sales\Api\CreditmemoManagementInterface::class);
        $titleMock = $this->getMockBuilder(\Magento\Framework\App\Action\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\RedirectFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\ForwardFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
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
        $this->actionFlagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
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
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Cancel::class,
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
        $e = new \Magento\Framework\Exception\LocalizedException(__($message));

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('creditmemo_id')
            ->willReturn($creditmemoId);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Sales\Api\CreditmemoManagementInterface::class)
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
            \Magento\Backend\Model\View\Result\Redirect::class,
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
            ->with(\Magento\Sales\Api\CreditmemoManagementInterface::class)
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
            \Magento\Backend\Model\View\Result\Redirect::class,
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
            \Magento\Backend\Model\View\Result\Forward::class,
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
            ->with(\Magento\Sales\Api\CreditmemoManagementInterface::class)
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
            \Magento\Backend\Model\View\Result\Redirect::class,
            $this->controller->execute()
        );
    }
}
