<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Action;

class AbstractActionTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\App\Action\AbstractAction|\PHPUnit\Framework\MockObject\MockObject */
    protected $action;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $request;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $response;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $redirectFactory;

    /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject */
    protected $redirect;

    /** @var \Magento\Framework\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject */
    protected $context;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);

        $this->redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->setMethods(['setRefererOrBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->redirect);

        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
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

        $this->action = $this->getMockForAbstractClass(
            \Magento\Framework\App\Action\AbstractAction::class,
            [$this->context]
        );
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
