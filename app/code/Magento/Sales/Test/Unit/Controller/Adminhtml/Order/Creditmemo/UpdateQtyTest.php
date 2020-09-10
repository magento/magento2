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
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Block\Order\Items;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\UpdateQty;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Creditmemo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class UpdateQtyTest extends TestCase
{
    /**
     * @var UpdateQty
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
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var JsonFactory|MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var RawFactory|MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var Json|MockObject
     */
    protected $resultJsonMock;

    /**
     * @var Raw|MockObject
     */
    protected $resultRawMock;

    /**
     * Set up method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInvoice', 'getOrder', 'cancel', 'getId'])
            ->getMock();
        $titleMock = $this->getMockBuilder(\Magento\Framework\App\Action\Title::class)
            ->disableOriginalConstructor()
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
        $this->loaderMock = $this->getMockBuilder(CreditmemoLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRawFactoryMock = $this->getMockBuilder(RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRawMock = $this->getMockBuilder(Raw::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->controller = $objectManager->getObject(
            UpdateQty::class,
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
        $e = new LocalizedException(__($message));
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
            Json::class,
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
            Json::class,
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

        $layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockMock = $this->getMockBuilder(Items::class)
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
            Raw::class,
            $this->controller->execute()
        );
    }
}
