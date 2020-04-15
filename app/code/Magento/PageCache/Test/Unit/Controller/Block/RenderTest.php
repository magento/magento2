<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Unit\Controller\Block;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\View;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\LayoutCacheKeyInterface;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\PageCache\Controller\Block;
use Magento\PageCache\Controller\Block\Render;
use Magento\PageCache\Test\Unit\Block\Controller\StubBlock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RenderTest extends TestCase
{
    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|MockObject
     */
    protected $responseMock;

    /**
     * @var View|MockObject
     */
    protected $viewMock;

    /**
     * @var Block
     */
    protected $action;

    /**
     * @var MockObject|InlineInterface
     */
    protected $translateInline;

    /**
     * @var Layout|MockObject
     */
    protected $layoutMock;

    /**
     * @var ProcessorInterface|MockObject
     */
    protected $layoutProcessorMock;

    /**
     * @var LayoutCacheKeyInterface|MockObject
     */
    protected $layoutCacheKeyMock;

    /**
     * Set up before test
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()->getMock();

        $this->layoutProcessorMock = $this->getMockForAbstractClass(
            ProcessorInterface::class
        );
        $this->layoutCacheKeyMock = $this->getMockForAbstractClass(
            LayoutCacheKeyInterface::class
        );

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->requestMock = $this->getMockBuilder(
            Http::class
        )->disableOriginalConstructor()->getMock();
        $this->responseMock = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()->getMock();
        $this->viewMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()->getMock();

        $this->layoutMock->expects($this->any())
            ->method('getUpdate')
            ->will($this->returnValue($this->layoutProcessorMock));

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewMock));

        $this->translateInline = $this->createMock(InlineInterface::class);

        $helperObjectManager = new ObjectManager($this);
        $this->action = $helperObjectManager->getObject(
            Render::class,
            [
                'context' => $contextMock,
                'translateInline' => $this->translateInline,
                'jsonSerializer' => new Json(),
                'base64jsonSerializer' => new Base64Json(),
                'layoutCacheKey' => $this->layoutCacheKeyMock
            ]
        );
    }

    public function testExecuteNotAjax()
    {
        $this->requestMock->expects($this->once())->method('isAjax')->will($this->returnValue(false));
        $this->requestMock->expects($this->once())->method('setActionName')->will($this->returnValue('noroute'));
        $this->requestMock->expects($this->once())->method('setDispatched')->will($this->returnValue(false));
        $this->layoutCacheKeyMock->expects($this->never())
            ->method('addCacheKeys');
        $this->action->execute();
    }

    /**
     * Test no params: blocks, handles
     */
    public function testExecuteNoParams()
    {
        $this->requestMock->expects($this->once())->method('isAjax')->will($this->returnValue(true));
        $this->requestMock->expects($this->at(10))
            ->method('getParam')
            ->with($this->equalTo('blocks'), $this->equalTo(''))
            ->will($this->returnValue(''));
        $this->requestMock->expects($this->at(11))
            ->method('getParam')
            ->with($this->equalTo('handles'), $this->equalTo(''))
            ->will($this->returnValue(''));
        $this->layoutCacheKeyMock->expects($this->never())
            ->method('addCacheKeys');
        $this->action->execute();
    }

    public function testExecute()
    {
        $blocks = ['block1', 'block2'];
        $handles = ['handle1', 'handle2'];
        $originalRequest = '{"route":"route","controller":"controller","action":"action","uri":"uri"}';
        $expectedData = ['block1' => 'data1', 'block2' => 'data2'];

        $blockInstance1 = $this->createPartialMock(
            StubBlock::class,
            ['toHtml']
        );
        $blockInstance1->expects($this->once())->method('toHtml')->will($this->returnValue($expectedData['block1']));

        $blockInstance2 = $this->createPartialMock(
            StubBlock::class,
            ['toHtml']
        );
        $blockInstance2->expects($this->once())->method('toHtml')->will($this->returnValue($expectedData['block2']));

        $this->requestMock->expects($this->once())->method('isAjax')->will($this->returnValue(true));

        $this->requestMock->expects($this->at(1))
            ->method('getRouteName')
            ->will($this->returnValue('magento_pagecache'));
        $this->requestMock->expects($this->at(2))
            ->method('getControllerName')
            ->will($this->returnValue('block'));
        $this->requestMock->expects($this->at(3))
            ->method('getActionName')
            ->will($this->returnValue('render'));
        $this->requestMock->expects($this->at(4))
            ->method('getRequestUri')
            ->will($this->returnValue('uri'));
        $this->requestMock->expects($this->at(5))
            ->method('getParam')
            ->with($this->equalTo('originalRequest'))
            ->will($this->returnValue($originalRequest));

        $this->requestMock->expects($this->at(10))
            ->method('getParam')
            ->with($this->equalTo('blocks'), $this->equalTo(''))
            ->will($this->returnValue(json_encode($blocks)));
        $this->requestMock->expects($this->at(11))
            ->method('getParam')
            ->with($this->equalTo('handles'), $this->equalTo(''))
            ->will($this->returnValue(base64_encode(json_encode($handles))));
        $this->viewMock->expects($this->once())->method('loadLayout')->with($this->equalTo($handles));
        $this->viewMock->expects($this->any())->method('getLayout')->will($this->returnValue($this->layoutMock));
        $this->layoutMock->expects($this->never())
            ->method('getUpdate');
        $this->layoutCacheKeyMock->expects($this->atLeastOnce())
            ->method('addCacheKeys');
        $this->layoutMock->expects($this->at(0))
            ->method('getBlock')
            ->with($this->equalTo($blocks[0]))
            ->will($this->returnValue($blockInstance1));
        $this->layoutMock->expects($this->at(1))
            ->method('getBlock')
            ->with($this->equalTo($blocks[1]))
            ->will($this->returnValue($blockInstance2));

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
