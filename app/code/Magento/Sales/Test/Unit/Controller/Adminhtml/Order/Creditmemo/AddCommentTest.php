<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

/**
 * Class AddCommentTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddCommentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\AddComment
     */
    protected $controller;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $loaderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $senderMock;

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
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultJsonMock;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRawMock;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
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
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->setMethods(['getRequest', 'getResponse', 'getObjectManager', 'getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->loaderMock = $this->getMockBuilder(\Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->senderMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Email\Sender\CreditmemoSender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRawFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRawMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\AddComment::class,
            [
                'context' => $this->contextMock,
                'creditmemoLoader' => $this->loaderMock,
                'creditmemoSender' => $this->senderMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'resultRawFactory' => $this->resultRawFactoryMock
            ]
        );
    }

    /**
     * Test execute module exception
     *
     * @return void
     */
    public function testExecuteModelException()
    {
        $message = 'Model exception';
        $e = new \Magento\Framework\Exception\LocalizedException(__($message));
        $response = ['error' => true, 'message' => $message];

        $this->requestMock->expects($this->any())
            ->method('setParam')
            ->will($this->throwException($e));
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($response)
            ->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Json::class,
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
        $message = 'Cannot add new comment.';
        $e = new \Exception($message);
        $response = ['error' => true, 'message' => $message];

        $this->requestMock->expects($this->any())
            ->method('setParam')
            ->will($this->throwException($e));
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($response)
            ->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Json::class,
            $this->controller->execute()
        );
    }

    /**
     * Test execute no comment
     *
     * @return void
     */
    public function testExecuteNoComment()
    {
        $message = 'The comment is missing. Enter and try again.';
        $response = ['error' => true, 'message' => $message];
        $data = [];

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('comment')
            ->willReturn($data);
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($response)
            ->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Json::class,
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
        $comment = 'Test comment';
        $data = ['comment' => $comment];
        $html = 'test output';

        $creditmemoMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockMock = $this->getMockBuilder(\Magento\Sales\Block\Adminhtml\Order\Creditmemo\View\Comments::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('comment')
            ->willReturn($data);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnArgument(0);
        $creditmemoMock->expects($this->once())
            ->method('addComment')
            ->willReturn($commentMock);
        $this->loaderMock->expects($this->once())
            ->method('load')
            ->willReturn($creditmemoMock);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->atLeastOnce())
            ->method('getLayout')
            ->willReturn($layoutMock);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('creditmemo_comments')
            ->willReturn($blockMock);
        $blockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);
        $this->resultRawFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRawMock);
        $this->resultRawMock->expects($this->once())
            ->method('setContents')
            ->with($html)
            ->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Raw::class,
            $this->controller->execute()
        );
    }
}
