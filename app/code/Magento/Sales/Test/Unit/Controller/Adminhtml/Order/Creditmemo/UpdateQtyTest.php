<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

/**
 * Class UpdateQtyTest
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class UpdateQtyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\UpdateQty
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonMock;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRawMock;

    /**
     * Set up method
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditmemoMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods(['getInvoice', 'getOrder', 'cancel', 'getId', '__wakeup'])
            ->getMock();
        $titleMock = $this->getMockBuilder('Magento\Framework\App\Action\Title')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\Manager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getHelper')
            ->will($this->returnValue($this->helperMock));
        $this->actionFlagMock = $this->getMockBuilder('Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $this->contextMock->expects($this->any())
            ->method('getActionFlag')
            ->will($this->returnValue($this->actionFlagMock));
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $this->contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $this->contextMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($titleMock));
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManagerMock));
        $this->loaderMock = $this->getMockBuilder('Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\JsonFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRawFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\RawFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRawMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Raw')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Creditmemo\UpdateQty',
            [
                'context' => $this->contextMock,
                'creditmemoLoader' => $this->loaderMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'resultRawFactory' => $this->resultRawFactoryMock
            ]
        );
    }

    /**
     * Test execute model exception
     *
     * @return void
     */
    public function testExecuteModelException()
    {
        $message = 'Model exception';
        $e = new \Magento\Framework\Exception\LocalizedException(__($message));
        $response = ['error' => true, 'message' => $message];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnArgument(0);
        $this->loaderMock->expects($this->once())
            ->method('load')
            ->willThrowException($e);
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($response)
            ->willReturnSelf();

        $this->assertInstanceOf(
            'Magento\Framework\Controller\Result\Json',
            $this->controller->execute()
        );
    }

    /**
     * Test execute exception
     *
     * @return void
     */
    public function testExecuteException()
    {
        $message = 'We can\'t update the item\'s quantity right now.';
        $e = new \Exception($message);
        $response = ['error' => true, 'message' => $message];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnArgument(0);
        $this->loaderMock->expects($this->once())
            ->method('load')
            ->willThrowException($e);
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($response)
            ->willReturnSelf();

        $this->assertInstanceOf(
            'Magento\Framework\Controller\Result\Json',
            $this->controller->execute()
        );
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecute()
    {
        $response = 'output';

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->withAnyParameters()
            ->willReturnArgument(0);

        $layoutMock = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();
        $blockMock = $this->getMockBuilder('Magento\Sales\Block\Order\Items')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->atLeastOnce())
            ->method('getLayout')
            ->willReturn($layoutMock);
        $blockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($response);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('order_items')
            ->willReturn($blockMock);
        $this->resultRawFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRawMock);
        $this->resultRawMock->expects($this->once())
            ->method('setContents')
            ->with($response)
            ->willReturnSelf();

        $this->assertInstanceOf(
            'Magento\Framework\Controller\Result\Raw',
            $this->controller->execute()
        );
    }
}
