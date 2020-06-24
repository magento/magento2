<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * Set up before test
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutProcessorMock = $this->getMockForAbstractClass(
            ProcessorInterface::class
        );
        $this->layoutCacheKeyMock = $this->getMockForAbstractClass(
            LayoutCacheKeyInterface::class
        );

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(
            Http::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutMock->expects($this->any())
            ->method('getUpdate')
            ->willReturn($this->layoutProcessorMock);

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);

        $this->translateInline = $this->getMockForAbstractClass(InlineInterface::class);

        $helperObjectManager = new ObjectManager($this);
        $this->action = $helperObjectManager->getObject(
            Render::class,
            [
                'context' => $contextMock,
                'translateInline' => $this->translateInline,
                'logger' => $this->loggerMock,
                'jsonSerializer' => new Json(),
                'base64jsonSerializer' => new Base64Json(),
                'layoutCacheKey' => $this->layoutCacheKeyMock
            ]
        );
    }

    public function testExecuteNotAjax()
    {
        $this->requestMock->expects($this->once())->method('isAjax')->willReturn(false);
        $this->requestMock->expects($this->once())->method('setActionName')->with('noroute');
        $this->requestMock->expects($this->once())->method('setDispatched')->with(false);
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
        $this->requestMock->expects($this->at(1))
            ->method('getRouteName')
            ->willReturn('magento_pagecache');
        $this->requestMock->expects($this->at(2))
            ->method('getControllerName')
            ->willReturn('render');
        $this->requestMock->expects($this->at(3))
            ->method('getActionName')
            ->willReturn('render_block');
        $this->requestMock->expects($this->at(4))
            ->method('getRequestUri')
            ->willReturn('uri');
        $this->requestMock->expects($this->at(9))
            ->method('getParam')
            ->with('originalRequest')
            ->willReturn('{"route":"route","controller":"controller","action":"action","uri":"uri"}');
        $this->requestMock->expects($this->at(14))
            ->method('getParam')
            ->with('blocks', '')
            ->willReturn('');
        $this->requestMock->expects($this->at(15))
            ->method('getParam')
            ->with('handles', '')
            ->willReturn('');
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
        $blockInstance1->expects($this->once())->method('toHtml')->willReturn($expectedData['block1']);

        $blockInstance2 = $this->createPartialMock(
            StubBlock::class,
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
        $this->requestMock->expects($this->at(9))
            ->method('getParam')
            ->with('originalRequest')
            ->willReturn($originalRequest);

        $this->requestMock->expects($this->at(14))
            ->method('getParam')
            ->with('blocks', '')
            ->willReturn(json_encode($blocks));
        $this->requestMock->expects($this->at(15))
            ->method('getParam')
            ->with('handles', '')
            ->willReturn(base64_encode(json_encode($handles)));
        $this->viewMock->expects($this->once())->method('loadLayout')->with($handles);
        $this->viewMock->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);
        $this->layoutMock->expects($this->never())
            ->method('getUpdate');
        $this->layoutCacheKeyMock->expects($this->atLeastOnce())
            ->method('addCacheKeys');
        $this->layoutMock->expects($this->at(0))
            ->method('getBlock')
            ->with($blocks[0])
            ->willReturn($blockInstance1);
        $this->layoutMock->expects($this->at(1))
            ->method('getBlock')
            ->with($blocks[1])
            ->willReturn($blockInstance2);

        $this->translateInline->expects($this->once())
            ->method('processResponseBody')
            ->with($expectedData)
            ->willReturnSelf();

        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with(json_encode($expectedData));

        $this->action->execute();
    }

    /**
     * Test execute with invalid origin request parameters.
     *
     * @param $currentRoute
     * @param $currentController
     * @param $currentAction
     * @param $currentRequestUri
     * @param $originalRequest
     * @dataProvider dataProviderOriginRequestInvalidParam
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteInvalidOriginRequestParams(
        $currentRoute,
        $currentController,
        $currentAction,
        $currentRequestUri,
        $originalRequest
    ) {
        $this->requestMock->expects($this->once())->method('isAjax')->willReturn(true);
        $this->requestMock->expects($this->at(1))
            ->method('getRouteName')
            ->willReturn($currentRoute);
        $this->requestMock->expects($this->at(2))
            ->method('getControllerName')
            ->willReturn($currentController);
        $this->requestMock->expects($this->at(3))
            ->method('getActionName')
            ->willReturn($currentAction);
        $this->requestMock->expects($this->at(4))
            ->method('getRequestUri')
            ->willReturn($currentRequestUri);
        $this->requestMock->expects($this->at(9))
            ->method('getParam')
            ->with('originalRequest')
            ->willReturn($originalRequest);
        $this->requestMock->expects($this->once())->method('setActionName')->with('noroute');
        $this->requestMock->expects($this->once())->method('setDispatched')->with(false);
        $this->layoutCacheKeyMock->expects($this->never())
            ->method('addCacheKeys');
        $this->action->execute();
    }

    /**
     * Test execute with invalid origin request JSON string.
     *
     * @param $currentRoute
     * @param $currentController
     * @param $currentAction
     * @param $currentRequestUri
     * @param $originalRequest
     * @dataProvider dataProviderOriginRequestInvalidJSON
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteInvalidOriginRequestJSON(
        $currentRoute,
        $currentController,
        $currentAction,
        $currentRequestUri,
        $originalRequest
    ) {
        $this->requestMock->expects($this->once())->method('isAjax')->willReturn(true);
        $this->requestMock->expects($this->at(1))
            ->method('getRouteName')
            ->willReturn($currentRoute);
        $this->requestMock->expects($this->at(2))
            ->method('getControllerName')
            ->willReturn($currentController);
        $this->requestMock->expects($this->at(3))
            ->method('getActionName')
            ->willReturn($currentAction);
        $this->requestMock->expects($this->at(4))
            ->method('getRequestUri')
            ->willReturn($currentRequestUri);
        $this->requestMock->expects($this->at(9))
            ->method('getParam')
            ->with('originalRequest')
            ->willReturn($originalRequest);
        $this->requestMock->expects($this->once())->method('setActionName')->with('noroute');
        $this->requestMock->expects($this->once())->method('setDispatched')->with(false);
        $this->layoutCacheKeyMock->expects($this->never())
            ->method('addCacheKeys');
        $this->loggerMock->expects($this->once())->method('critical');
        $this->action->execute();
    }

    /**
     * Test execute method for the request with empty current request parameters.
     *
     * @param $currentRoute
     * @param $currentController
     * @param $currentAction
     * @param $currentRequestUri
     * @dataProvider dataProviderEmptyCurrentRequestParams
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteEmptyCurrentRequestParams(
        $currentRoute,
        $currentController,
        $currentAction,
        $currentRequestUri
    ) {
        $this->requestMock->expects($this->once())->method('isAjax')->willReturn(true);
        $this->requestMock->expects($this->at(1))
            ->method('getRouteName')
            ->willReturn($currentRoute);
        $this->requestMock->expects($this->at(2))
            ->method('getControllerName')
            ->willReturn($currentController);
        $this->requestMock->expects($this->at(3))
            ->method('getActionName')
            ->willReturn($currentAction);
        $this->requestMock->expects($this->at(4))
            ->method('getRequestUri')
            ->willReturn($currentRequestUri);
        $this->requestMock->expects($this->once())->method('setActionName')->with('noroute');
        $this->requestMock->expects($this->once())->method('setDispatched')->with(false);
        $this->layoutCacheKeyMock->expects($this->never())
            ->method('addCacheKeys');
        $this->action->execute();
    }

    /**
     * Data provider for testExecuteInvalidOriginRequestJSON method.
     *
     * @return array
     */
    public function dataProviderOriginRequestInvalidJSON()
    {
        return [
            [
                'route',
                'controller',
                'currentAction',
                'currentRequestUri',
                '{"controller"|\&&&%%%%%:"controller","action":"action","uri":"uri"}'
            ],
            [
                'route',
                'controller',
                'currentAction',
                'currentRequestUri',
                '["routes":"route","controller":"controller","action":"action","uri":"uri"]'
            ]
        ];
    }

    /**
     * Data provider for testExecuteEmptyCurrentRequestParams method.
     *
     * @return array
     */
    public function dataProviderEmptyCurrentRequestParams()
    {
        return [
            [
                '',
                'controller',
                'currentAction',
                'currentRequestUri',
            ],
            [
                'route',
                '',
                'currentAction',
                'currentRequestUri',
            ],
            [
                'route',
                'controller',
                '',
                'currentRequestUri',
            ],
            [
                'route',
                'controller',
                'currentAction',
                '',
            ],
            [
                '',
                '',
                '',
                ''
            ]
        ];
    }

    /**
     * Data provider for testExecuteInvalidOriginRequestParams method.
     *
     * @return array
     */
    public function dataProviderOriginRequestInvalidParam()
    {
        return [
            [
                'route',
                'controller',
                'currentAction',
                'currentRequestUri',
                '{"controller":"controller","action":"action","uri":"uri"}'
            ],
            [
                'route',
                'controller',
                'currentAction',
                'currentRequestUri',
                '{"routes":"route","controller":"controller","action":"action","uri":"uri"}'
            ],
            [
                'route',
                'controller',
                'currentAction',
                'currentRequestUri',
                '{"routes":"","controller":"","action":"","uri":""}'
            ]
        ];
    }
}
