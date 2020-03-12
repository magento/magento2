<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Result;

use Magento\Theme\Controller\Result\AsyncCssPlugin;
use Magento\Framework\App\Response\Http;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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
                "content" => "<head><link rel=\"stylesheet\" href=\"css/async.css\">" .
                    "<style>.critical-css{}</style>" .
                    "</head>",
                "flag" => true,
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
                "flag" => true,
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
                "flag" => false,
                "result" => "<head><link rel=\"stylesheet\" href=\"css/async.css\">" .
                    "<link rel=\"preload\" href=\"other-file.html\">" .
                    "</head>",
            ],
            [
                "content" => "<head><link rel=\"stylesheet\" href=\"css/first.css\">" .
                    "<link rel=\"stylesheet\" href=\"css/second.css\">" .
                    "<style>.critical-css{}</style>" .
                    "</head>",
                "flag" => true,
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
                "flag" => false,
                "result" => "<head><style>.critical-css{}</style></head>"
            ],
            [
                "content" => "<head><style>.critical-css{}</style></head>",
                "flag" => true,
                "result" => "<head><style>.critical-css{}</style></head>"
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
