<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\View;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Block\Adminhtml\Order\Invoice\View\Comments;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\AddComment;
use Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AddCommentTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $commentSenderMock;

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
    protected $viewMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    protected $pageTitleMock;

    /**
     * @var AddComment
     */
    protected $controller;

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
     * @var Json|MockObject
     */
    protected $resultJsonMock;

    /**
     * @var InvoiceRepositoryInterface|MockObject
     */
    protected $invoiceRepository;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getRequest',
                    'getResponse',
                    'getObjectManager',
                    'getSession',
                    'getHelper',
                    'getActionFlag',
                    'getMessageManager',
                    'getResultRedirectFactory',
                    'getView'
                ]
            )
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getView')
            ->willReturn($this->viewMock);
        $this->viewMock->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRawFactoryMock = $this->getMockBuilder(RawFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->commentSenderMock = $this->getMockBuilder(InvoiceCommentSender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceRepository = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            AddComment::class,
            [
                'context' => $contextMock,
                'invoiceCommentSender' => $this->commentSenderMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultRawFactory' => $this->resultRawFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'invoiceRepository' => $this->invoiceRepository
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $this->controller,
            'invoiceRepository',
            $this->invoiceRepository
        );
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecute(): void
    {
        $data = ['comment' => 'test comment'];
        $invoiceId = 2;
        $response = 'some result';

        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['id'] => $invoiceId,
                ['invoice_id'] => $invoiceId
            });
        $this->requestMock
            ->method('getPost')
            ->with('comment')
            ->willReturn($data);

        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('addComment')
            ->with($data['comment'], false, false);
        $this->invoiceRepository->expects($this->once())
            ->method('save')
            ->with($invoiceMock);

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $commentsBlockMock = $this->getMockBuilder(Comments::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commentsBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($response);

        $layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('invoice_comments')
            ->willReturn($commentsBlockMock);

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $this->resultPageMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->commentSenderMock->expects($this->once())
            ->method('send')
            ->with($invoiceMock, false, $data['comment']);

        $resultRaw = $this->getMockBuilder(Raw::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRaw->expects($this->once())->method('setContents')->with($response);

        $this->resultRawFactoryMock->expects($this->once())->method('create')->willReturn($resultRaw);
        $this->assertSame($resultRaw, $this->controller->execute());
    }

    /**
     * Test execute model exception
     *
     * @return void
     */
    public function testExecuteModelException(): void
    {
        $message = 'model exception';
        $response = ['error' => true, 'message' => $message];
        $e = new LocalizedException(__($message));

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willThrowException($e);

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);

        $this->resultJsonMock->expects($this->once())->method('setData')->with($response);
        $this->assertSame($this->resultJsonMock, $this->controller->execute());
    }

    /**
     * Test execute exception
     *
     * @return void
     */
    public function testExecuteException(): void
    {
        $response = ['error' => true, 'message' => 'Cannot add new comment.'];
        $error = new \Exception('test');

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willThrowException($error);

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);

        $this->resultJsonMock->expects($this->once())->method('setData')->with($response);
        $this->assertSame($this->resultJsonMock, $this->controller->execute());
    }
}
