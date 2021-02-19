<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Unit\Controller\Block;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\PageCache\Controller\Block
     */
    protected $action;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutProcessorMock;

    /**
     * @var \Magento\Framework\View\Layout\LayoutCacheKeyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutCacheKeyMock;

    /**
     * Set up before test
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()->getMock();

        $this->layoutProcessorMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Layout\ProcessorInterface::class
        );
        $this->layoutCacheKeyMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Layout\LayoutCacheKeyInterface::class
        );

        $contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->requestMock = $this->getMockBuilder(
            \Magento\Framework\App\Request\Http::class
        )->disableOriginalConstructor()->getMock();
        $this->responseMock = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()->getMock();
        $this->viewMock = $this->getMockBuilder(\Magento\Framework\App\View::class)
            ->disableOriginalConstructor()->getMock();

        $this->layoutMock->expects($this->any())
            ->method('getUpdate')
            ->willReturn($this->layoutProcessorMock);

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);

        $this->translateInline = $this->createMock(\Magento\Framework\Translate\InlineInterface::class);

        $helperObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->action = $helperObjectManager->getObject(
            \Magento\PageCache\Controller\Block\Render::class,
            [
                'context' => $contextMock,
                'translateInline' => $this->translateInline,
                'jsonSerializer' => new \Magento\Framework\Serialize\Serializer\Json(),
                'base64jsonSerializer' => new \Magento\Framework\Serialize\Serializer\Base64Json(),
                'layoutCacheKey' => $this->layoutCacheKeyMock
            ]
        );
    }

    public function testExecuteNotAjax()
    {
        $this->requestMock->expects($this->once())->method('isAjax')->willReturn(false);
        $this->requestMock->expects($this->once())->method('setActionName')->willReturn('noroute');
        $this->requestMock->expects($this->once())->method('setDispatched')->willReturn(false);
        $this->layoutCacheKeyMock->expects($this->never())
            ->method('addCacheKeys');
        $this->action->execute();
    }

    /**
     * Test no params: blocks, handles
     */
    public function testExecuteNoParams()
    {
        $this->requestMock->expects($this->once())->method('isAjax')->willReturn(true);
        $this->requestMock->expects($this->at(6))
            ->method('getParam')
            ->with($this->equalTo('blocks'), $this->equalTo(''))
            ->willReturn('');
        $this->requestMock->expects($this->at(7))
            ->method('getParam')
            ->with($this->equalTo('handles'), $this->equalTo(''))
            ->willReturn('');
        $this->layoutCacheKeyMock->expects($this->never())
            ->method('addCacheKeys');
        $this->action->execute();
    }

    public function testExecute()
    {
        $blocks = ['block1', 'block2'];
        $handles = ['handle1', 'handle2', "'handle'", '@hanle', '"hanle', '*hanle', '.hanle'];
        $originalRequest = '{"route":"route","controller":"controller","action":"action","uri":"uri"}';
        $expectedData = ['block1' => 'data1', 'block2' => 'data2'];

        $blockInstance1 = $this->createPartialMock(
            \Magento\PageCache\Test\Unit\Block\Controller\StubBlock::class,
            ['toHtml']
        );
        $blockInstance1->expects($this->once())->method('toHtml')->willReturn($expectedData['block1']);

        $blockInstance2 = $this->createPartialMock(
            \Magento\PageCache\Test\Unit\Block\Controller\StubBlock::class,
            ['toHtml']
        );
        $blockInstance2->expects($this->once())->method('toHtml')->willReturn($expectedData['block2']);

        $this->requestMock->expects($this->once())->method('isAjax')->willReturn(true);

        $this->requestMock->expects($this->at(1))
            ->method('getRouteName')
            ->willReturn('magento_pagecache');
        $this->requestMock->expects($this->at(2))
            ->method('getControllerName')
            ->willReturn('block');
        $this->requestMock->expects($this->at(3))
            ->method('getActionName')
            ->willReturn('render');
        $this->requestMock->expects($this->at(4))
            ->method('getRequestUri')
            ->willReturn('uri');
        $this->requestMock->expects($this->at(5))
            ->method('getParam')
            ->with($this->equalTo('originalRequest'))
            ->willReturn($originalRequest);

        $this->requestMock->expects($this->at(10))
            ->method('getParam')
            ->with($this->equalTo('blocks'), $this->equalTo(''))
            ->willReturn(json_encode($blocks));
        $this->requestMock->expects($this->at(11))
            ->method('getParam')
            ->with($this->equalTo('handles'), $this->equalTo(''))
            ->willReturn(base64_encode(json_encode($handles)));
        $this->viewMock->expects($this->once())->method('loadLayout')->with($this->equalTo(['handle1', 'handle2']));
        $this->viewMock->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);
        $this->layoutMock->expects($this->never())
            ->method('getUpdate');
        $this->layoutCacheKeyMock->expects($this->atLeastOnce())
            ->method('addCacheKeys');
        $this->layoutMock->expects($this->at(0))
            ->method('getBlock')
            ->with($this->equalTo($blocks[0]))
            ->willReturn($blockInstance1);
        $this->layoutMock->expects($this->at(1))
            ->method('getBlock')
            ->with($this->equalTo($blocks[1]))
            ->willReturn($blockInstance2);

        $this->translateInline->expects($this->once())
            ->method('processResponseBody')
            ->with($expectedData)
            ->willReturnSelf();

        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with($this->equalTo(json_encode($expectedData)));

        $this->action->execute();
    }
}
