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
namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

/**
 * Class AddCommentTest
 */
class AddCommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\AddComment
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
    protected $senderMock;

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
    protected $viewMock;

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
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->viewMock = $this->getMockBuilder('Magento\Backend\Model\View')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
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
            ->method('getView')
            ->will($this->returnValue($this->viewMock));
        $this->loaderMock = $this->getMockBuilder('Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->senderMock = $this->getMockBuilder('Magento\Sales\Model\Order\Email\Sender\CreditmemoSender')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->controller = new \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\AddComment(
            $this->contextMock,
            $this->loaderMock,
            $this->senderMock
        );
    }

    public function testExecuteModelException()
    {
        $message = 'Model exception';
        $e = new \Magento\Framework\Model\Exception($message);
        $response = ['error' => true, 'message' => $message];

        $this->requestMock->expects($this->any())
            ->method('setParam')
            ->will($this->throwException($e));
        $helperMock = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $helperMock->expects($this->once())
            ->method('jsonEncode')
            ->with($response)
            ->willReturn(json_encode($response));
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Core\Helper\Data')
            ->willReturn($helperMock);

        $this->assertNull($this->controller->execute());
    }

    public function testExecuteException()
    {
        $message = 'Cannot add new comment.';
        $e = new \Exception($message);
        $response = ['error' => true, 'message' => $message];

        $this->requestMock->expects($this->any())
            ->method('setParam')
            ->will($this->throwException($e));
        $helperMock = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $helperMock->expects($this->once())
            ->method('jsonEncode')
            ->with($response)
            ->willReturn(json_encode($response));
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Core\Helper\Data')
            ->willReturn($helperMock);

        $this->assertNull($this->controller->execute());
    }

    public function testExecuteNoComment()
    {
        $message = 'The Comment Text field cannot be empty.';
        $response = ['error' => true, 'message' => $message];
        $data = [];

        $helperMock = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $helperMock->expects($this->once())
            ->method('jsonEncode')
            ->with($response)
            ->willReturn(json_encode($response));

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('comment')
            ->willReturn($data);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Core\Helper\Data')
            ->willReturn($helperMock);

        $this->assertNull($this->controller->execute());
    }

    public function testExecute()
    {
        $comment = 'Test comment';
        $data = ['comment' => $comment];
        $html = 'test output';

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('comment')
            ->willReturn($data);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->withAnyParameters()
            ->willReturnArgument(0);
        $creditmemoMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $commentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo\Comment')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $creditmemoMock->expects($this->once())
            ->method('addComment')
            ->withAnyParameters()
            ->willReturn($commentMock);
        $this->loaderMock->expects($this->once())
            ->method('load')
            ->willReturn($creditmemoMock);
        $layoutMock = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $blockMock = $this->getMockBuilder('Magento\Sales\Block\Adminhtml\Order\Creditmemo\View\Comments')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $blockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('creditmemo_comments')
            ->willReturn($blockMock);
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->assertNull($this->controller->execute());
    }
}
