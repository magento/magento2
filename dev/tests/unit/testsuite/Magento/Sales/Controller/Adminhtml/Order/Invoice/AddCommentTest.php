<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;

/**
 * Class AddCommentTest
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 */
class AddCommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceLoaderMock;

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
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\AddComment
     */
    protected $controller;

    public function setUp()
    {
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
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods([])
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

        $this->invoiceLoaderMock = $this->getMockBuilder('Magento\Sales\Controller\Adminhtml\Order\InvoiceLoader')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->commentSenderMock = $this->getMockBuilder('Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->controller = new \Magento\Sales\Controller\Adminhtml\Order\Invoice\AddComment(
            $contextMock,
            $this->invoiceLoaderMock,
            $this->commentSenderMock
        );
    }

    public function testExecute()
    {
        $data = ['comment' => 'test comment'];
        $orderId = 1;
        $invoiceId = 2;
        $invoiceData = [];
        $response = 'some result';

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('comment')
            ->will($this->returnValue($data));
        $this->requestMock->expects($this->at(3))
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue($orderId));
        $this->requestMock->expects($this->at(4))
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));
        $this->requestMock->expects($this->at(5))
            ->method('getParam')
            ->with('invoice', [])
            ->will($this->returnValue($invoiceData));

        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('addComment')
            ->with($data['comment'], false, false);
        $invoiceMock->expects($this->once())
            ->method('save');

        $this->invoiceLoaderMock->expects($this->once())
            ->method('load')
            ->with($orderId, $invoiceId, $invoiceData)
            ->will($this->returnValue($invoiceMock));

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
