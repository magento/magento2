<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\InvoiceRepositoryInterface;

/**
 * Class AddCommentTest
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AddCommentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $commentSenderMock;

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
    protected $viewMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageTitleMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\AddComment
     */
    protected $controller;

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
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultJsonMock;

    /**
     * @var InvoiceRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
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

        $titleMock = $this->getMockBuilder(\Magento\Framework\App\Action\Title::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->viewMock = $this->getMockBuilder(\Magento\Framework\App\View::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
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
            ->method('getTitle')
            ->willReturn($titleMock);
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

        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultJsonMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->resultRawFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->commentSenderMock = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender::class
        )->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->invoiceRepository = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Invoice\AddComment::class,
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
    public function testExecute()
    {
        $data = ['comment' => 'test comment'];
        $invoiceId = 2;
        $response = 'some result';

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('id')
            ->willReturn($invoiceId);
        $this->requestMock->expects($this->at(1))
            ->method('setParam');
        $this->requestMock->expects($this->at(2))
            ->method('getPost')
            ->with('comment')
            ->willReturn($data);
        $this->requestMock->expects($this->at(3))
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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

        $commentsBlockMock = $this->getMockBuilder(\Magento\Sales\Block\Adminhtml\Order\Invoice\View\Comments::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $commentsBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($response);

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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

        $resultRaw = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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
    public function testExecuteModelException()
    {
        $message = 'model exception';
        $response = ['error' => true, 'message' => $message];
        $e = new \Magento\Framework\Exception\LocalizedException(__($message));

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->will($this->throwException($e));

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
    public function testExecuteException()
    {
        $response = ['error' => true, 'message' => 'Cannot add new comment.'];
        $error = new \Exception('test');

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->will($this->throwException($error));

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJsonMock);

        $this->resultJsonMock->expects($this->once())->method('setData')->with($response);
        $this->assertSame($this->resultJsonMock, $this->controller->execute());
    }
}
