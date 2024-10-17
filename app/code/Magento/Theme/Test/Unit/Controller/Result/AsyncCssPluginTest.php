<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Result;

use Magento\Theme\Controller\Result\AsyncCssPlugin;
use Magento\Csp\Api\InlineUtilInterface;
use Magento\Framework\App\Response\Http;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Magento\Theme\Test\Unit\Controller\Result\AsyncCssPlugin.
 */
class AsyncCssPluginTest extends TestCase
{
    private const STUB_XML_PATH_USE_CSS_CRITICAL_PATH = 'dev/css/use_css_critical_path';

    /**
     * @var AsyncCssPlugin
     */
    private $plugin;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Http|MockObject
     */
    private $httpMock;

    /**
     * @var Layout|MockObject
     */
    private $layoutMock;

    /**
     * @var InlineUtilInterface|MockObject
     */
    private $cspInlineUtilMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->onlyMethods(['isSetFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->httpMock = $this->createMock(Http::class);
        $this->layoutMock = $this->createMock(Layout::class);
        $this->cspInlineUtilMock = $this->createMock(InlineUtilInterface::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->plugin = $objectManager->getObject(
            AsyncCssPlugin::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'cspInlineUtil' => $this->cspInlineUtilMock
            ]
        );
    }

    /**
     * Data Provider for testAfterRenderResult
     *
     * @return array
     */
    public static function renderResultDataProvider(): array
    {
        return [
            [
                "content" => "<head><link rel=\"stylesheet\" href=\"css/async.css\">" .
                    "<style>.critical-css{}</style>" .
                    "</head>",
                "isSetFlag" => true,
                "result" => "<head><style>.critical-css{}</style>\n" .
                    "<link " .
                        "rel=\"stylesheet\" media=\"print\" onload=\"this.onload=null;this.media='all'\" " .
                        "href=\"css/async.css\">\n" .
                    "</head>",
            ],
            [
                "content" => "<head><link rel=\"stylesheet\" href=\"css/async.css\">" .
                    "<link rel=\"preload\" href=\"other-file.html\">" .
                    "</head>",
                "isSetFlag" => true,
                "result" => "<head><link rel=\"preload\" href=\"other-file.html\">\n" .
                    "<link " .
                        "rel=\"stylesheet\" media=\"print\" onload=\"this.onload=null;this.media='all'\" " .
                        "href=\"css/async.css\">\n" .
                    "</head>",
            ],
            [
                "content" => "<head><link rel=\"stylesheet\" href=\"css/async.css\">" .
                    "<link rel=\"preload\" href=\"other-file.html\">" .
                    "</head>",
                "isSetFlag" => false,
                "result" => "<head><link rel=\"stylesheet\" href=\"css/async.css\">" .
                    "<link rel=\"preload\" href=\"other-file.html\">" .
                    "</head>",
            ],
            [
                "content" => "<head><link rel=\"stylesheet\" href=\"css/first.css\">" .
                    "<link rel=\"stylesheet\" href=\"css/second.css\">" .
                    "<style>.critical-css{}</style>" .
                    "</head>",
                "isSetFlag" => true,
                "result" => "<head><style>.critical-css{}</style>\n" .
                    "<link " .
                        "rel=\"stylesheet\" media=\"print\" onload=\"this.onload=null;this.media='all'\" " .
                        "href=\"css/first.css\">\n" .
                    "<link " .
                        "rel=\"stylesheet\" media=\"print\" onload=\"this.onload=null;this.media='all'\" " .
                        "href=\"css/second.css\">\n" .
                    "</head>",
            ],
            [
                "content" => "<head><style>.critical-css{}</style></head>",
                "isSetFlag" => false,
                "result" => "<head><style>.critical-css{}</style></head>"
            ],
            [
                "content" => "<head><style>.critical-css{}</style></head>",
                "isSetFlag" => true,
                "result" => "<head><style>.critical-css{}</style></head>"
            ]
        ];
    }

    /**
     * Test after render result response
     *
     * @param string $content
     * @param bool $isSetFlag
     * @param string $result
     * @return void
     * @dataProvider renderResultDataProvider
     */
    public function testAfterRenderResult(string $content, bool $isSetFlag, string $result): void
    {
        // Given (context)
        $this->httpMock->method('getContent')
            ->willReturn($content);

        $this->scopeConfigMock->method('isSetFlag')
            ->with(self::STUB_XML_PATH_USE_CSS_CRITICAL_PATH, ScopeInterface::SCOPE_STORE)
            ->willReturn($isSetFlag);

        if ($isSetFlag) {
            $this->cspInlineUtilMock->expects($this->any())
                ->method('renderEventListener')
                ->with(
                    'onload',
                    "this.onload=null;this.media='all'"
                )->willReturn('onload="this.onload=null;this.media=\'all\'"');
        }
        // Expects
        $this->httpMock->expects($this->any())
            ->method('setContent')
            ->with($result);

        // When
        $this->plugin->afterRenderResult($this->layoutMock, $this->layoutMock, $this->httpMock);
    }

    /**
     * Data Provider for testAfterRenderResultIfGetContentIsNotAString()
     *
     * @return array
     */
    public static function ifGetContentIsNotAStringDataProvider(): array
    {
        return [
            [
                'content' => null
            ]
        ];
    }

    /**
     * Test AfterRenderResult if content is not a string
     *
     * @param $content
     * @return void
     * @dataProvider ifGetContentIsNotAStringDataProvider
     */
    public function testAfterRenderResultIfGetContentIsNotAString($content): void
    {
        $this->scopeConfigMock->method('isSetFlag')
            ->with(self::STUB_XML_PATH_USE_CSS_CRITICAL_PATH, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $this->httpMock->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        $this->httpMock->expects($this->never())
            ->method('setContent');

        $this->plugin->afterRenderResult($this->layoutMock, $this->layoutMock, $this->httpMock);
    }
}
