<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class UnholdTest
 */
class UnholdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Unhold
     */
    protected $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            [],
            [],
            '',
            false
        );
        $resultRedirectFactory = $this->getMock(
            'Magento\Backend\Model\View\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );
        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
        $this->messageManager = $this->getMock(
            'Magento\Framework\Message\Manager',
            ['addSuccess', 'addError'],
            [],
            '',
            false
        );
        $this->orderRepositoryMock = $this->getMockBuilder('Magento\Sales\Api\OrderRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validatorMock = $this->getMockBuilder('Magento\Framework\Data\Form\FormKey\Validator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirect = $this->getMock('Magento\Backend\Model\View\Result\Redirect', [], [], '', false);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->context->expects($this->once())->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->once())->method('getResponse')->willReturn($this->response);
        $this->context->expects($this->once())->method('getObjectManager')->willReturn($this->objectManager);
        $this->context->expects($this->once())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $this->context->expects($this->once())->method('getFormKeyValidator')->willReturn($this->validatorMock);

        $this->controller = $objectManagerHelper->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Unhold',
            [
                'context' => $this->context,
                'request' => $this->request,
                'response' => $this->response,
                'orderRepository' => $this->orderRepositoryMock
            ]
        );
    }

    public function testExecuteNotPost()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with('Can\'t unhold order.');
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/*/')
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->controller->execute());
    }
}
