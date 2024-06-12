<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Page;

use Magento\Cms\Controller\Page\View;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Result\Page;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    private const STUB_PAGE_ID = 2;

    /**
     * @var View
     */
    protected $controller;

    /**
     * @var MockObject
     */
    protected $pageHelperMock;

    /**
     * @var MockObject|RequestInterface
     */
    protected $requestMock;

    /**
     * @var MockObject|ForwardFactory
     */
    protected $forwardFactoryMock;

    /**
     * @var MockObject|Forward
     */
    protected $forwardMock;

    /**
     * @var MockObject|Page
     */
    protected $resultPageMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->forwardMock);

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->pageHelperMock = $this->createMock(PageHelper::class);

        $this->controller = $objectManager->getObject(
            View::class,
            [
                'request' => $this->requestMock,
                'pageHelper' => $this->pageHelperMock,
                'resultForwardFactory' => $this->forwardFactoryMock
            ]
        );
    }

    public function testExecuteResultPage()
    {
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['page_id', null, self::STUB_PAGE_ID],
                    ['id', null, self::STUB_PAGE_ID]
                ]
            );
        $this->pageHelperMock->expects($this->once())
            ->method('prepareResultPage')
            ->with($this->controller, self::STUB_PAGE_ID)
            ->willReturn($this->resultPageMock);
        $this->assertSame($this->resultPageMock, $this->controller->execute());
    }

    public function testExecuteResultForward()
    {
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['page_id', null, self::STUB_PAGE_ID],
                    ['id', null, self::STUB_PAGE_ID]
                ]
            );
        $this->forwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();
        $this->assertSame($this->forwardMock, $this->controller->execute());
    }
}
