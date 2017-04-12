<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Page;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Controller\Page\View
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmsHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $forwardFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $forwardMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var string
     */
    protected $pageId = '2';

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $responseMock = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\ForwardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->forwardMock);

        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->cmsHelperMock = $this->getMock(\Magento\Cms\Helper\Page::class, [], [], '', false);
        $objectManagerMock->expects($this->once())->method('get')->willReturn($this->cmsHelperMock);
        $this->controller = $helper->getObject(
            \Magento\Cms\Controller\Page\View::class,
            [
                'response' => $responseMock,
                'objectManager' => $objectManagerMock,
                'request' => $this->requestMock,
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
                    ['page_id', $this->pageId, $this->pageId],
                    ['id', false, $this->pageId]
                ]
            );
        $this->cmsHelperMock->expects($this->once())
            ->method('prepareResultPage')
            ->with($this->controller, $this->pageId)
            ->willReturn($this->resultPageMock);
        $this->assertSame($this->resultPageMock, $this->controller->execute());
    }

    public function testExecuteResultForward()
    {
        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['page_id', $this->pageId, $this->pageId],
                    ['id', false, $this->pageId]
                ]
            );
        $this->forwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();
        $this->assertSame($this->forwardMock, $this->controller->execute());
    }
}
