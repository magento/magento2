<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\App\Request\Http;

use Laminas\Stdlib\Parameters;
use Laminas\Uri\Http as HttpUri;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
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

    /** @var Parameters|MockObject */
    private $fileParams;

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
}
