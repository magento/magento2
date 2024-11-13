<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\View\Comments;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\AddComment;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Comment;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddCommentTest extends TestCase
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
     * SetUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getRequest', 'getResponse', 'getObjectManager'])
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
        $this->loaderMock = $this->getMockBuilder(CreditmemoLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->senderMock = $this->getMockBuilder(CreditmemoSender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRawFactoryMock = $this->getMockBuilder(RawFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
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
            AddComment::class,
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
        $e = new LocalizedException(__($message));
        $response = ['error' => true, 'message' => $message];

        $this->requestMock->expects($this->any())
            ->method('setParam')
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
        $message = 'Cannot add new comment.';
        $e = new \Exception($message);
        $response = ['error' => true, 'message' => $message];

        $this->requestMock->expects($this->any())
            ->method('setParam')
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
        $comment = 'Test comment';
        $data = ['comment' => $comment];
        $html = 'test output';

        $creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commentMock = $this->getMockBuilder(Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockMock = $this->getMockBuilder(Comments::class)
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
            Raw::class,
            $this->controller->execute()
        );
    }
}
