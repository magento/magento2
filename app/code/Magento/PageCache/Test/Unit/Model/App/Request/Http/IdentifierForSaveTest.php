<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\App\Request\Http;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PageCache\Model\App\Request\Http\IdentifierForSave;
use Magento\PageCache\Model\App\Request\Http\IdentifierStoreReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

        $this->identifierStoreReader = $this->getMockBuilder(IdentifierStoreReader::class)
            ->onlyMethods(['getPageTagsWithStoreCacheTags'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->identifierMock = $this->getMockBuilder(Identifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
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
     *
     * @return void
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

        $this->contextMock->expects($this->any())
            ->method('getVaryString')
            ->willReturn(self::VARY);

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
                        'http://example.com/path1/?abc=123',
                        self::VARY
                    ]
                )
            ),
            $this->model->getValue()
        );
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
