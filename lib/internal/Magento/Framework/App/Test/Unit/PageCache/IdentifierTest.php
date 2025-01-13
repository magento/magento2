<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\PageCache;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IdentifierTest extends TestCase
{
    /**
     * Test value for cache vary string
     */
    public const VARY = '123';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var HttpRequest|MockObject
     */
    private $requestMock;

    /**
     * @var Identifier
     */
    private $model;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(HttpRequest::class)
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

        $this->model = $this->objectManager->getObject(
            Identifier::class,
            [
                'request'    => $this->requestMock,
                'context'    => $this->contextMock,
                'serializer' => $this->serializerMock
            ]
        );
        parent::setUp();
    }

    /**
     * @return void
     */
    public function testSecureDifferentiator(): void
    {
        $this->requestMock
            ->method('isSecure')
            ->willReturnOnConsecutiveCalls(true, false);
        $this->requestMock->method('getUriString')
            ->willReturn('http://example.com/path/');
        $this->contextMock->method('getVaryString')->willReturn(self::VARY);

        $valueWithSecureRequest = $this->model->getValue();
        $valueWithInsecureRequest = $this->model->getValue();
        $this->assertNotEquals($valueWithSecureRequest, $valueWithInsecureRequest);
    }

    /**
     * @return void
     */
    public function testDomainDifferentiator(): void
    {
        $this->requestMock->method('isSecure')->willReturn(true);
        $this->requestMock
            ->method('getUriString')
            ->willReturnOnConsecutiveCalls('http://example.com/path/', 'http://example.net/path/');
        $this->contextMock->method('getVaryString')->willReturn(self::VARY);

        $valueDomain1 = $this->model->getValue();
        $valueDomain2 = $this->model->getValue();
        $this->assertNotEquals($valueDomain1, $valueDomain2);
    }

    /**
     * @return void
     */
    public function testPathDifferentiator(): void
    {
        $this->requestMock->method('isSecure')->willReturn(true);
        $this->requestMock
            ->method('getUriString')
            ->willReturnOnConsecutiveCalls('http://example.com/path/', 'http://example.com/path1/');
        $this->contextMock->method('getVaryString')->willReturn(self::VARY);

        $valuePath1 = $this->model->getValue();
        $valuePath2 = $this->model->getValue();
        $this->assertNotEquals($valuePath1, $valuePath2);
    }

    /**
     * @param $cookieExists
     *
     * @return void
     * @dataProvider trueFalseDataProvider
     */
    public function testVaryStringSource($cookieExists): void
    {
        $this->requestMock->method('get')->willReturn($cookieExists ? 'vary-string-from-cookie' : null);
        $this->contextMock->expects($cookieExists ? $this->never() : $this->once())->method('getVaryString');
        $this->model->getValue();
    }

    /**
     * @return array
     */
    public static function trueFalseDataProvider(): array
    {
        return [[true], [false]];
    }

    /**
     * Test get identifier value.
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

        $this->contextMock->expects($this->any())
            ->method('getVaryString')
            ->willReturn(self::VARY);

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
     * Test get identifier value with marketing parameters.
     *
     * @return void
     */
    public function testGetValueWithMarketingParameters(): void
    {
        $this->requestMock->expects($this->any())
            ->method('isSecure')
            ->willReturn(true);

        $this->requestMock->expects($this->any())
            ->method('getUriString')
            ->willReturn('http://example.com/path1/?abc=123&gclid=456&utm_source=abc');

        $this->contextMock->expects($this->any())
            ->method('getVaryString')
            ->willReturn(self::VARY);

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
}
