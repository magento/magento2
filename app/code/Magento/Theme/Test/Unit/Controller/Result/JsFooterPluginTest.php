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

    /**
     * @var JsFooterPlugin
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['isSetFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->httpMock = $this->createMock(Http::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->plugin = $objectManager->getObject(
            JsFooterPlugin::class,
            [
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * Data Provider for testBeforeSendResponse()
     *
     * @return array
     */
    public function sendResponseDataProvider(): array
    {
        return [
            'content_with_script_tag' => [
                "content" => "<body><h1>Test Title</h1>" .
                    "<script type=\"text/x-magento-init\">test</script>" .
                    "<script type=\"text/x-magento-template\">test</script>" .
                    "<p>Test Content</p></body>",
                "flag" => true,
                "result" => "<body><h1>Test Title</h1>" .
                    "<script type=\"text/x-magento-template\">test</script>" .
                    "<p>Test Content</p>" .
                    "<script type=\"text/x-magento-init\">test</script>" .
                    "\n</body>"
            ],
            'content_with_config_disable' => [
                "content" => "<body><p>Test Content</p></body>",
                "flag" => false,
                "result" => "<body><p>Test Content</p></body>"
            ],
            'content_without_script_tag' => [
                "content" => "<body><p>Test Content</p></body>",
                "flag" => true,
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
     * @dataProvider sendResponseDataProvider
     */
    public function testBeforeSendResponse($content, $isSetFlag, $result): void
    {
        $this->httpMock->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                self::STUB_XML_PATH_DEV_MOVE_JS_TO_BOTTOM,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($isSetFlag);

        $this->httpMock->expects($this->any())
            ->method('setContent')
            ->with($result);

        $this->plugin->beforeSendResponse($this->httpMock);
    }

    /**
     * Data Provider for testBeforeSendResponseIfGetContentIsNotAString()
     *
     * @return array
     */
    public function ifGetContentIsNotAStringDataProvider(): array
    {
        return [
            'empty_array' => [
                'content' => []
            ],
            'null' => [
                'content' => null
            ]
        ];
    }

    /**
     * Test BeforeSendResponse if content is not a string
     *
     * @param string $content
     * @return void
     * @dataProvider ifGetContentIsNotAStringDataProvider
     */
    public function testBeforeSendResponseIfGetContentIsNotAString($content): void
    {
        $this->httpMock->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        $this->scopeConfigMock->expects($this->never())
            ->method('isSetFlag')
            ->with(
                self::STUB_XML_PATH_DEV_MOVE_JS_TO_BOTTOM,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(false);

        $this->plugin->beforeSendResponse($this->httpMock);
    }
}
