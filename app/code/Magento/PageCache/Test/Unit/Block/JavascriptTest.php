<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Block;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Block\Javascript;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\PageCache\Block\Javascript
 */
class JavascriptTest extends TestCase
{
    use MockCreationTrait;

    private const COOKIE_NAME = 'private_content_version';

    /**
     * @var Javascript|MockObject
     */
    protected $blockJavascript;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var ProcessorInterface|MockObject
     */
    protected $layoutUpdateMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->createPartialMockWithReflection(
            RequestInterface::class,
            [
                // All RequestInterface methods
                'getModuleName', 'setModuleName', 'getActionName', 'setActionName',
                'getParam', 'setParams', 'getParams', 'getCookie', 'isSecure',
                // Custom methods
                'getControllerName', 'getRequestUri', 'setRequestUri', 'getRouteName'
            ]
        );
        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->layoutUpdateMock = $this->createMock(ProcessorInterface::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);
        $this->layoutMock->expects($this->any())
            ->method('getUpdate')
            ->willReturn($this->layoutUpdateMock);
        $objectManager = new ObjectManager($this);
        $this->blockJavascript = $objectManager->getObject(
            Javascript::class,
            [
                'context' => $this->contextMock
            ]
        );
    }

    /**
     * @covers \Magento\PageCache\Block\Javascript::getScriptOptions
     * @param bool $isSecure
     * @param string $url
     * @param string $expectedResult
     */
    #[DataProvider('getScriptOptionsDataProvider')]
    public function testGetScriptOptions($isSecure, $url, $expectedResult)
    {
        $handles = [
            'some',
            'handles',
            'here'
        ];
        $this->requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn($isSecure);
        $this->requestMock->expects($this->once())
            ->method('getRouteName')
            ->willReturn('route');
        $this->requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn('controller');
        $this->requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn('action');
        $this->requestMock->expects($this->once())
            ->method('getRequestUri')
            ->willReturn('uri');
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);
        $this->layoutUpdateMock->expects($this->once())
            ->method('getHandles')
            ->willReturn($handles);
        $this->assertMatchesRegularExpression($expectedResult, $this->blockJavascript->getScriptOptions());
    }

    /**
     * @return array
     */
    public static function getScriptOptionsDataProvider()
    {
        return [
            'http' => [
                'isSecure' => false,
                'url' => 'http://some-name.com/page_cache/block/render',
                'expectedResult' => '~http:\\\\/\\\\/some-name\\.com.+\\["some","handles","here"\\]~'
            ],
            'https' => [
                'isSecure' => true,
                'url' => 'https://some-name.com/page_cache/block/render',
                'expectedResult' => '~https:\\\\/\\\\/some-name\\.com.+\\["some","handles","here"\\]~'
            ]
        ];
    }

    /**
     * @covers \Magento\PageCache\Block\Javascript::getScriptOptions
     * @param string $url
     * @param string $route
     * @param string $controller
     * @param string $action
     * @param string $uri
     * @param string $expectedResult
     */
    #[DataProvider('getScriptOptionsPrivateContentDataProvider')]
    public function testGetScriptOptionsPrivateContent($url, $route, $controller, $action, $uri, $expectedResult)
    {
        $handles = [
            'some',
            'handles',
            'here'
        ];
        $this->requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn(false);

        $this->requestMock->expects($this->once())
            ->method('getRouteName')
            ->willReturn($route);

        $this->requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn($controller);

        $this->requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn($action);

        $this->requestMock->expects($this->once())
            ->method('getRequestUri')
            ->willReturn($uri);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);

        $this->layoutUpdateMock->expects($this->once())
            ->method('getHandles')
            ->willReturn($handles);
        $this->assertMatchesRegularExpression($expectedResult, $this->blockJavascript->getScriptOptions());
    }

    /**
     * @return array
     */
    public static function getScriptOptionsPrivateContentDataProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            'http' => [
                'url'            => 'http://some-name.com/page_cache/block/render',
                'route'          => 'route',
                'controller'     => 'controller',
                'action'         => 'action',
                'uri'            => 'uri',
                'expectedResult' => '~"originalRequest":{"route":"route","controller":"controller","action":"action","uri":"uri"}~'
            ],
        ];
        //@codingStandardsIgnoreEnd
    }
}
