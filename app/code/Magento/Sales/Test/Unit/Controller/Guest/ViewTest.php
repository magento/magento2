<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Guest;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Controller\Guest\View
     */
    protected $viewController;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Sales\Helper\Guest|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $guestHelperMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMock();
        $this->guestHelperMock = $this->getMockBuilder(\Magento\Sales\Helper\Guest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->objectManagerHelper->getObject(
            \Magento\Framework\App\Action\Context::class,
            [
                'request' => $this->requestMock
            ]
        );
        $this->viewController = $this->objectManagerHelper->getObject(
            \Magento\Sales\Controller\Guest\View::class,
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
