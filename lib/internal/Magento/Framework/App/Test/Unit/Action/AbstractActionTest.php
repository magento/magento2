<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Action;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractActionTest extends TestCase
{
    /** @var AbstractAction|MockObject */
    protected $action;

    /** @var RequestInterface|MockObject */
    protected $request;

    /** @var ResponseInterface|MockObject */
    protected $response;

    /** @var RedirectInterface|MockObject */
    protected $redirectFactory;

    /** @var Redirect|MockObject */
    protected $redirect;

    /** @var Context|MockObject */
    protected $context;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->response = $this->getMockForAbstractClass(ResponseInterface::class);

        $this->redirect = $this->getMockBuilder(Redirect::class)
            ->onlyMethods(['setRefererOrBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->redirect);

        $this->context = $this->getMockBuilder(Context::class)
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
            AbstractAction::class,
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
