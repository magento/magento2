<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Action;

class AbstractActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\Action\AbstractAction|\PHPUnit_Framework_MockObject_MockObject */
    protected $action;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectFactory;

    /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirect;

    /** @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    public function setUp()
    {
        $this->request = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()->getMock();
        $this->response = $this->getMock('Magento\Framework\App\ResponseInterface', [], [], '', false);

        $this->redirect = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->setMethods(['setRefererOrBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectFactory = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->redirect);

        $this->context = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectFactory);
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->action = $this->getMockForAbstractClass('Magento\Framework\App\Action\AbstractAction', [$this->context]);
    }

    public function testGetRequest()
    {
        $this->assertEquals($this->request, $this->action->getRequest());
    }

    public function testGetResponse()
    {
        $this->assertEquals($this->response, $this->action->getResponse());
    }
}
