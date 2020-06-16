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
use Magento\Theme\Controller\Result\AsyncCssPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Theme\Test\Unit\Controller\Result\AsyncCssPlugin.
 */
class AsyncCssPluginTest extends TestCase
{
    const STUB_XML_PATH_USE_CSS_CRITICAL_PATH = 'dev/css/use_css_critical_path';

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
            AsyncCssPlugin::class,
            [
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * Data Provider for before send response
     *
     * @return array
     */
    public function sendResponseDataProvider(): array
    {
        return [
            [
                "content" => "<body><h1>Test Title</h1>" .
                    "<link rel=\"stylesheet\" href=\"css/critical.css\" />" .
                    "<p>Test Content</p></body>",
                "flag" => true,
                "result" => "<body><h1>Test Title</h1>" .
                    "<link rel=\"preload\" as=\"style\" media=\"all\"" .
                    " onload=\"this.onload=null;this.rel='stylesheet'\" href=\"css/critical.css\" />" .
                    "<p>Test Content</p>" .
                    "<link rel=\"stylesheet\" href=\"css/critical.css\" />" .
                    "\n</body>"
            ],
            [
                "content" => "<body><p>Test Content</p></body>",
                "flag" => false,
                "result" => "<body><p>Test Content</p></body>"
            ],
            [
                "content" => "<body><p>Test Content</p></body>",
                "flag" => true,
                "result" => "<body><p>Test Content</p></body>"
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
                self::STUB_XML_PATH_USE_CSS_CRITICAL_PATH,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($isSetFlag);

        $this->httpMock->expects($this->any())
            ->method('setContent')
            ->with($result);

        $this->plugin->beforeSendResponse($this->httpMock);
    }

    /**
     * Test BeforeSendResponse if content is not a string
     *
     * @return void
     */
    public function testIfGetContentIsNotAString(): void
    {
        $this->httpMock->expects($this->once())
            ->method('getContent')
            ->willReturn([]);

        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(
                self::STUB_XML_PATH_USE_CSS_CRITICAL_PATH,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(false);

        $this->plugin->beforeSendResponse($this->httpMock);
    }
}
