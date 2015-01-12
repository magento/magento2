<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class AddCommentTest
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 */
class AddCommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentSenderMock;

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
    protected $viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitleMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\AddComment
     */
    protected $controller;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $titleMock = $this->getMockBuilder('Magento\Framework\App\Action\Title')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->viewMock = $this->getMockBuilder('Magento\Backend\Model\View')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($titleMock));
        $contextMock->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($this->viewMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $this->viewMock->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);

        $this->commentSenderMock = $this->getMockBuilder('Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->controller = $objectManager->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Invoice\AddComment',
            [
                'context' => $contextMock,
                'invoiceCommentSender' => $this->commentSenderMock
            ]
        );
    }

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

        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('addComment')
            ->with($data['comment'], false, false);
        $invoiceMock->expects($this->once())
            ->method('save');
        $invoiceMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Sales\Model\Order\Invoice')
            ->willReturn($invoiceMock);

        $commentsBlockMock = $this->getMockBuilder('Magento\Sales\Block\Adminhtml\Order\Invoice\View\Comments')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $commentsBlockMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($response));

        $layoutMock = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('invoice_comments')
            ->will($this->returnValue($commentsBlockMock));

        $this->viewMock->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));

        $this->commentSenderMock->expects($this->once())
            ->method('send')
            ->with($invoiceMock, false, $data['comment']);

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($response);

        $this->assertNull($this->controller->execute());
    }

    public function testExecuteModelException()
    {
        $message = 'model exception';
        $response = ['error' => true, 'message' => $message];
        $e = new \Magento\Framework\Model\Exception($message);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->will($this->throwException($e));

        $helperMock = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $helperMock->expects($this->once())
            ->method('jsonEncode')
            ->with($response)
            ->will($this->returnValue(json_encode($response)));

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with(json_encode($response));

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Core\Helper\Data')
            ->will($this->returnValue($helperMock));
        $this->assertNull($this->controller->execute());
    }

    public function testExecuteException()
    {
        $response = ['error' => true, 'message' => 'Cannot add new comment.'];
        $e = new \Exception('test');

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->will($this->throwException($e));

        $helperMock = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $helperMock->expects($this->once())
            ->method('jsonEncode')
            ->with($response)
            ->will($this->returnValue(json_encode($response)));

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Core\Helper\Data')
            ->will($this->returnValue($helperMock));

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with(json_encode($response));

        $this->assertNull($this->controller->execute());
    }
}
