<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Result;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Result\Layout;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Controller\Result\JsFooterPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Theme\Test\Unit\Controller\Result\JsFooterPlugin.
 */
class JsFooterPluginTest extends TestCase
{
    const STUB_XML_PATH_DEV_MOVE_JS_TO_BOTTOM = 'dev/js/move_script_to_bottom';

    /** @var JsFooterPlugin */
    private $plugin;

    /** @var ScopeConfigInterface|MockObject */
    private $scopeConfigMock;

    /** @var Http|MockObject */
    private $httpMock;

    /** @var Layout|MockObject */
    private $layoutMock;

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

        $objectManager = new ObjectManagerHelper($this);
        $this->plugin = $objectManager->getObject(
            JsFooterPlugin::class,
            [
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * Data Provider for testAfterRenderResult()
     *
     * @return array
     */
    public static function renderResultDataProvider(): array
    {
        return [
            'content_with_script_tag' => [
                "content" => "<body><h1>Test Title</h1>" .
                    "<script type=\"text/x-magento-init\">test</script>" .
                    "<script type=\"text/x-magento-template\">test</script>" .
                    "<p>Test Content</p></body>",
                "isSetFlag" => true,
                "result" => "<body><h1>Test Title</h1>" .
                    "<script type=\"text/x-magento-template\">test</script>" .
                    "<p>Test Content</p>\n" .
                    "<script type=\"text/x-magento-init\">test</script>\n" .
                    "</body>"
            ],
            'content_with_config_disable' => [
                "content" => "<body><p>Test Content</p></body>",
                "isSetFlag" => false,
                "result" => "<body><p>Test Content</p></body>"
            ],
            'content_without_script_tag' => [
                "content" => "<body><p>Test Content</p></body>",
                "isSetFlag" => true,
                "result" => "<body><p>Test Content</p>\n</body>"
            ]
        ];
    }

    /**
     * Test beforeSendResponse
     *
     * @param string $content
     * @param bool $isSetFlag
     * @param string $result
     * @return void
     * @dataProvider renderResultDataProvider
     */
    public function testAfterRenderResult($content, $isSetFlag, $result): void
    {
        // Given (context)
        $this->httpMock->method('getContent')
            ->willReturn($content);

        $this->scopeConfigMock->method('isSetFlag')
            ->with(self::STUB_XML_PATH_DEV_MOVE_JS_TO_BOTTOM, ScopeInterface::SCOPE_STORE)
            ->willReturn($isSetFlag);

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
            'null' => [
                'content' => null
            ]
        ];
    }

    /**
     * Test AfterRenderResult if content is not a string
     *
     * @param string $content
     * @return void
     * @dataProvider ifGetContentIsNotAStringDataProvider
     */
    public function testAfterRenderResultIfGetContentIsNotAString($content): void
    {
        $this->scopeConfigMock->method('isSetFlag')
            ->with(self::STUB_XML_PATH_DEV_MOVE_JS_TO_BOTTOM, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $this->httpMock->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        $this->httpMock->expects($this->never())
            ->method('setContent');

        $this->plugin->afterRenderResult($this->layoutMock, $this->layoutMock, $this->httpMock);
    }
}
