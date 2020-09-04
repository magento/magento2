<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\Guest\View;
use Magento\Sales\Helper\Guest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @var View
     */
    protected $viewController;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var Guest|MockObject
     */
    protected $guestHelperMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $this->guestHelperMock = $this->getMockBuilder(Guest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->objectManagerHelper->getObject(
            Context::class,
            [
                'request' => $this->requestMock
            ]
        );
        $this->viewController = $this->objectManagerHelper->getObject(
            View::class,
            [
                'context' => $this->context,
                'guestHelper' => $this->guestHelperMock,
                'resultPageFactory' => $this->resultPageFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteOrderLoaded()
    {
        $this->guestHelperMock->expects($this->once())
            ->method('loadValidOrder')
            ->with($this->requestMock)
            ->willReturn(true);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);
        $this->guestHelperMock->expects($this->once())
            ->method('getBreadcrumbs')
            ->with($this->resultPageMock);

        $this->assertSame($this->resultPageMock, $this->viewController->execute());
    }

    /**
     * @return void
     */
    public function testExecuteOrderNotFound()
    {
        $this->guestHelperMock->expects($this->once())
            ->method('loadValidOrder')
            ->with($this->requestMock)
            ->willReturn($this->resultRedirectMock);

        $this->assertSame($this->resultRedirectMock, $this->viewController->execute());
    }
}
