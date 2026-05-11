<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\App\Request\Http;

use Laminas\Stdlib\Parameters;
use Laminas\Uri\Http as HttpUri;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PageCache\Model\App\Request\Http\IdentifierForSave;
use Magento\PageCache\Model\App\Request\Http\IdentifierStoreReader;
use Magento\Framework\App\Response\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IdentifierForSaveTest extends TestCase
{
    /**
     * Test value for cache vary string
     */
    private const VARY = '123';

    /**
     * @var Context|MockObject
     */
    private mixed $contextMock;

    /**
     * @var HttpRequest|MockObject
     */
    private mixed $requestMock;

    /**
     * @var IdentifierForSave
     */
    private IdentifierForSave $model;

    /**
     * @var Json|MockObject
     */
    private mixed $serializerMock;
    /**
     * @var IdentifierStoreReader|MockObject
     */
    private $identifierStoreReader;

    /** @var Parameters|MockObject */
    private $fileParams;

    /**
     * @var Identifier
     */
    private $identifierMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->onlyMethods(['serialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $this->fileParams = $this->createMock(Parameters::class);

        $this->identifierStoreReader = $this->getMockBuilder(IdentifierStoreReader::class)
            ->onlyMethods(['getPageTagsWithStoreCacheTags'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->identifierMock = $this->getMockBuilder(Identifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturn($this->identifierMock);
        ObjectManager::setInstance($objectManagerMock);

        $this->model = new IdentifierForSave(
            $this->requestMock,
            $this->contextMock,
            $this->serializerMock,
            $this->identifierStoreReader
        );
        parent::setUp();
    }

    /**
     * Test get identifier for save value.
     */
    public function testGetValue(): void
    {
        $this->identifierMock->expects($this->once())
            ->method('getMarketingParameterPatterns')
            ->willReturn($this->getpattern());

        $this->requestMock->expects($this->any())
            ->method('isSecure')
            ->willReturn(true);

        $this->requestMock->expects($this->any())
            ->method('getUriString')
            ->willReturn('http://example.com/path1/');

        $this->requestMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($this->fileParams);

        $this->fileParams->expects($this->any())
            ->method('toArray')
            ->willReturn([]);

        $this->contextMock->expects($this->any())
            ->method('getVaryString')
            ->willReturn(self::VARY);

        $uri = $this->createMock(HttpUri::class);
        $uri->expects($this->any())->method('getQueryAsArray')->willReturn('');
        $this->requestMock->expects($this->any())
            ->method('getUri')
            ->willReturn($uri);

        $this->identifierStoreReader->method('getPageTagsWithStoreCacheTags')->willReturnCallback(
            function ($value) {
                return $value;
            }
        );

        $this->assertEquals(
            sha1(
                json_encode(
                    [
                        true,
                        'http://example.com/path1/',
                        '',
                        self::VARY
                    ]
                )
            ),
            $this->model->getValue()
        );
    }

    /**
     * Test get identifier for save value with query parameters.
     *
     * @return void
     */
    public function testGetValueWithQuery(): void
    {
        $this->requestMock->expects($this->any())
            ->method('isSecure')
            ->willReturn(true);

        $this->requestMock->expects($this->any())
            ->method('getUriString')
            ->willReturn('http://example.com/path1/?b=2&a=1');

        $this->requestMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($this->fileParams);

        $this->fileParams->expects($this->any())
            ->method('toArray')
            ->willReturn([
                'b' => 2,
                'a' => 1,
            ]);

        $this->contextMock->expects($this->any())
            ->method('getVaryString')
            ->willReturn(self::VARY);

        $uri = $this->createMock(HttpUri::class);
        $uri->expects($this->any())->method('getQueryAsArray')->willReturn([
            'b' => 2,
            'a' => 1,
        ]);
        $this->requestMock->expects($this->any())
            ->method('getUri')
            ->willReturn($uri);

        $this->identifierStoreReader->method('getPageTagsWithStoreCacheTags')->willReturnCallback(
            function ($value) {
                return $value;
            }
        );

        $this->assertEquals(
            sha1(
                json_encode(
                    [
                        true,
                        'http://example.com/path1/',
                        'a=1&b=2',
                        self::VARY
                    ]
                )
            ),
            $this->model->getValue()
        );
    }

    /**
     * Test get identifier for save value with marketing parameters.
     *
     * @return void
     */
    public function testGetValueWithMarketingParameters(): void
    {
        $this->identifierMock->expects($this->any())
            ->method('getMarketingParameterPatterns')
            ->willReturn($this->getPattern());

        $this->requestMock->expects($this->any())
            ->method('isSecure')
            ->willReturn(true);

        $this->requestMock->expects($this->any())
            ->method('getUriString')
            ->willReturn('http://example.com/path1/?abc=123&gclid=456&utm_source=abc');

        $this->contextMock->expects($this->any())
            ->method('getVaryString')
            ->willReturn(self::VARY);

        $uri = $this->createMock(HttpUri::class);
        $uri->expects($this->any())->method('getQueryAsArray')->willReturn(['abc' => '123']);
        $this->requestMock->expects($this->any())
            ->method('getUri')
            ->willReturn($uri);

        $this->identifierStoreReader->method('getPageTagsWithStoreCacheTags')->willReturnCallback(
            function ($value) {
                return $value;
            }
        );
        $this->assertEquals(
            sha1(
                json_encode(
                    [
                        true,
                        'http://example.com/path1/',
                        'abc=123',
                        self::VARY
                    ]
                )
            ),
            $this->model->getValue()
        );
    }

    /**
     * Test vary string resolution from cookie or context fallback.
     *
     * @param string|null $cookieVaryString
     * @param string $contextVaryString
     * @param string $expectedVaryString
     * @param bool $expectContextCall
     * @return void
     * @covers \Magento\PageCache\Model\App\Request\Http\IdentifierForSave::getValue
     */
    #[DataProvider('varyStringDataProvider')]
    public function testGetValueVaryStringResolution(
        ?string $cookieVaryString,
        string $contextVaryString,
        string $expectedVaryString,
        bool $expectContextCall
    ): void {
        $this->identifierMock->expects($this->once())
            ->method('getMarketingParameterPatterns')
            ->willReturn([]);
        $this->requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getUriString')
            ->willReturn('http://example.com/path1/');
        $this->requestMock->expects($this->once())
            ->method('get')
            ->with(Http::COOKIE_VARY_STRING)
            ->willReturn($cookieVaryString);

        $this->contextMock->expects($expectContextCall ? $this->once() : $this->never())
            ->method('getVaryString')
            ->willReturn($contextVaryString);

        $uri = $this->createMock(HttpUri::class);
        $uri->expects($this->once())
            ->method('getQueryAsArray')
            ->willReturn([]);
        $this->requestMock->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);
        $this->identifierStoreReader->expects($this->once())
            ->method('getPageTagsWithStoreCacheTags')
            ->willReturnArgument(0);

        $expected = sha1(json_encode([true, 'http://example.com/path1/', '', $expectedVaryString]));
        $this->assertSame($expected, $this->model->getValue());
    }

    /**
     * Data provider for vary string resolution tests.
     *
     * @return array
     */
    public static function varyStringDataProvider(): array
    {
        return [
            'cookie vary string takes precedence' => [
                'cookie_vary_value',
                'context_vary_value',
                'cookie_vary_value',
                false
            ],
            'fallback to context when cookie is null' => [
                null,
                'context_vary_value',
                'context_vary_value',
                true
            ],
            'fallback to context when cookie is empty' => [
                '',
                'context_vary_value',
                'context_vary_value',
                true
            ],
        ];
    }

    /**
     * @return string[]
     */
    public function getPattern(): array
    {
        return [
            '/&?gad_source\=[^&]+/',
            '/&?gbraid\=[^&]+/',
            '/&?wbraid\=[^&]+/',
            '/&?_gl\=[^&]+/',
            '/&?dclid\=[^&]+/',
            '/&?gclsrc\=[^&]+/',
            '/&?srsltid\=[^&]+/',
            '/&?msclkid\=[^&]+/',
            '/&?_kx\=[^&]+/',
            '/&?gclid\=[^&]+/',
            '/&?cx\=[^&]+/',
            '/&?ie\=[^&]+/',
            '/&?cof\=[^&]+/',
            '/&?siteurl\=[^&]+/',
            '/&?zanpid\=[^&]+/',
            '/&?origin\=[^&]+/',
            '/&?fbclid\=[^&]+/',
            '/&?mc_(.*?)\=[^&]+/',
            '/&?utm_(.*?)\=[^&]+/',
            '/&?_bta_(.*?)\=[^&]+/',
        ];
    }
}
