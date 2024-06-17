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
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\RegexFactory;
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
     * Validation pattern for handles array
     */
    private const VALIDATION_RULE_PATTERN = '/^[a-z0-9]+[a-z0-9_]*$/i';

    /**
     * @inheritDoc
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

        $this->layoutMock->expects($this->any())
            ->method('getUpdate')
            ->willReturn($this->layoutProcessorMock);

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);

        $this->translateInline = $this->getMockForAbstractClass(InlineInterface::class);

        $regexFactoryMock = $this->getMockBuilder(RegexFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $regexObject = new Regex(self::VALIDATION_RULE_PATTERN);

        $regexFactoryMock->expects($this->any())->method('create')
            ->willReturn($regexObject);

        $helperObjectManager = new ObjectManager($this);
        $this->action = $helperObjectManager->getObject(
            Render::class,
            [
                'context' => $contextMock,
                'translateInline' => $this->translateInline,
                'jsonSerializer' => new Json(),
                'base64jsonSerializer' => new Base64Json(),
                'layoutCacheKey' => $this->layoutCacheKeyMock,
                'regexValidatorFactory' => $regexFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteNotAjax(): void
    {
        $this->requestMock->expects($this->once())->method('isAjax')->willReturn(false);
        $this->requestMock->expects($this->once())->method('setActionName')->willReturn('noroute');
        $this->requestMock->expects($this->once())->method('setDispatched')->willReturn(false);
        $this->layoutCacheKeyMock->expects($this->never())
            ->method('addCacheKeys');
        $this->action->execute();
    }

    /**
     * Test no params: blocks, handles.
     *
     * @return void
     */
    public function testExecuteNoParams(): void
    {
        $this->requestMock->expects($this->once())->method('isAjax')->willReturn(true);
        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(
                function ($arg1, $arg2) {
                    if (empty($arg1) && empty($arg2)) {
                        return null;
                    } elseif ($arg1 === 'blocks' && $arg2 === '') {
                        return '';
                    } elseif ($arg1 === 'handles' && $arg2 === '') {
                        return '';
                    }
                }
            );
        $this->layoutCacheKeyMock->expects($this->never())
            ->method('addCacheKeys');
        $this->action->execute();
    }

    /**
     * @return void
     */
    public function testExecute(): void
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

        $this->requestMock
            ->method('getRouteName')
            ->willReturn('magento_pagecache');
        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(
                function ($arg1, $arg2 = '') use ($originalRequest, $blocks, $handles) {
                    if ($arg1 === 'originalRequest') {
                        return $originalRequest;
                    } elseif ($arg1 === 'blocks' && $arg2 === '') {
                        return json_encode($blocks);
                    } elseif ($arg1 === 'handles' && $arg2 === '') {
                        return base64_encode(json_encode($handles));
                    }
                }
            );
        $this->requestMock
            ->method('getRequestUri')
            ->willReturn('uri');
        $this->requestMock
            ->method('getActionName')
            ->willReturn('render');
        $this->requestMock
            ->method('getControllerName')
            ->willReturn('block');
        $this->viewMock->expects($this->once())->method('loadLayout')->with($handles);
        $this->viewMock->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);
        $this->layoutMock->expects($this->never())
            ->method('getUpdate');
        $this->layoutCacheKeyMock->expects($this->atLeastOnce())
            ->method('addCacheKeys');
        $this->layoutMock
            ->method('getBlock')
            ->willReturnCallback(
                function ($arg1) use ($blocks, $blockInstance1, $blockInstance2) {
                    if ($arg1 === $blocks[0]) {
                        return $blockInstance1;
                    } elseif ($arg1 === $blocks[1]) {
                        return $blockInstance2;
                    }
                }
            );

        $this->translateInline->expects($this->once())
            ->method('processResponseBody')
            ->with($expectedData)
            ->willReturnSelf();

        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with(json_encode($expectedData));

        $this->action->execute();
    }
}
