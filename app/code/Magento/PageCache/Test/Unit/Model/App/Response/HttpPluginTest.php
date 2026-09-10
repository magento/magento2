<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\App\Response;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Stdlib\DateTime;
use Magento\MediaStorage\Model\File\Storage\Response as FileResponse;
use Magento\PageCache\Model\App\Response\HttpPlugin;
use Laminas\Http\Header\CacheControl;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests \Magento\PageCache\Model\App\Response\HttpPlugin.
 */
class HttpPluginTest extends TestCase
{
    /**
     * @var HttpPlugin
     */
    private $httpPlugin;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var HttpRequest|MockObject
     */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->createMock(Context::class);
        $this->request = $this->createMock(HttpRequest::class);
        $this->httpPlugin = new HttpPlugin(
            $this->context,
            $this->request,
            new DateTime()
        );
    }

    /**
     * @param string $responseClass
     * @param bool $headersSent
     * @param int $sendVaryCalled
     * @return void
     */
    #[DataProvider('beforeSendResponseDataProvider')]
    public function testBeforeSendResponse(string $responseClass, bool $headersSent, int $sendVaryCalled): void
    {
        /** @var HttpResponse|MockObject $responseMock */
        $responseMock = $this->createMock($responseClass);
        $responseMock->expects($this->any())->method('headersSent')->willReturn($headersSent);
        $responseMock->expects($this->exactly($sendVaryCalled))->method('sendVary');

        $this->httpPlugin->beforeSendResponse($responseMock);
    }

    /**
     * @return array
     */
    public static function beforeSendResponseDataProvider(): array
    {
        return [
            'http_response_headers_not_sent' => [HttpResponse::class, false, 1],
            'http_response_headers_sent' => [HttpResponse::class, true, 0],
            'file_response_headers_not_sent' => [FileResponse::class, false, 0],
            'file_response_headers_sent' => [FileResponse::class, true, 0],
        ];
    }

    public function testBeforeSendResponseVaryMismatch()
    {
        /** @var HttpResponse|MockObject $responseMock */
        $this->context->expects($this->any())->method('getVaryString')->willReturn('currentVary');
        $this->request->expects($this->any())->method('get')->willReturn('varyCookie');
        /** @var HttpResponse|MockObject $responseMock */
        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->once())->method('setNoCacheHeaders');
        $responseMock->expects($this->once())->method('sendVary');

        $this->httpPlugin->beforeSendResponse($responseMock);
    }

    public function testBeforeSendResponseVaryNotSet()
    {
        /** @var HttpResponse|MockObject $responseMock */
        $this->context->expects($this->any())->method('getVaryString')->willReturn('currentVary');
        $this->request->expects($this->any())->method('get')->willReturn(null);
        /** @var HttpResponse|MockObject $responseMock */
        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->never())->method('setNoCacheHeaders');
        $responseMock->expects($this->once())->method('sendVary');
        $this->httpPlugin->beforeSendResponse($responseMock);
    }

    /**
     * A page that is public for the shared cache must not be reusable by the browser without revalidation:
     * keep s-maxage for Varnish / CDNs, force max-age=0 for the browser (mirrors the built-in cache mode).
     *
     * @return void
     */
    public function testBeforeSendResponsePreventsBrowserCachingOfPublicResponses(): void
    {
        $this->context->expects($this->any())->method('getVaryString')->willReturn(null);
        $this->request->expects($this->any())->method('get')->willReturn(null);
        /** @var HttpResponse|MockObject $responseMock */
        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->any())
            ->method('getHeader')
            ->with('Cache-Control')
            ->willReturn(CacheControl::fromString('Cache-Control: public, max-age=86400, s-maxage=86400'));
        $headers = [];
        $responseMock->expects($this->atLeastOnce())
            ->method('setHeader')
            ->willReturnCallback(
                function (string $name, $value, bool $replace = false) use (&$headers, $responseMock) {
                    $headers[strtolower($name)] = [$value, $replace];
                    return $responseMock;
                }
            );
        $responseMock->expects($this->never())->method('setNoCacheHeaders');
        $responseMock->expects($this->once())->method('sendVary');

        $this->httpPlugin->beforeSendResponse($responseMock);

        $this->assertArrayHasKey('cache-control', $headers);
        $cacheControl = CacheControl::fromString('Cache-Control: ' . $headers['cache-control'][0]);
        $this->assertTrue($headers['cache-control'][1], 'Cache-Control must replace the existing header');
        $this->assertTrue($cacheControl->hasDirective('public'));
        $this->assertSame('0', (string)$cacheControl->getDirective('max-age'));
        $this->assertSame('86400', (string)$cacheControl->getDirective('s-maxage'));
        $this->assertArrayHasKey('expires', $headers);
        $this->assertLessThan(time(), strtotime($headers['expires'][0]), 'Expires must be in the past');
    }

    /**
     * Responses that are not public for the shared cache are left untouched.
     *
     * @param string|null $cacheControl
     * @return void
     */
    #[DataProvider('nonPublicCacheControlDataProvider')]
    public function testBeforeSendResponseLeavesNonPublicResponsesUntouched(?string $cacheControl): void
    {
        $this->context->expects($this->any())->method('getVaryString')->willReturn(null);
        $this->request->expects($this->any())->method('get')->willReturn(null);
        /** @var HttpResponse|MockObject $responseMock */
        $responseMock = $this->createMock(HttpResponse::class);
        $responseMock->expects($this->any())
            ->method('getHeader')
            ->with('Cache-Control')
            ->willReturn($cacheControl === null ? false : CacheControl::fromString('Cache-Control: ' . $cacheControl));
        $responseMock->expects($this->never())->method('setHeader');
        $responseMock->expects($this->never())->method('setNoCacheHeaders');
        $responseMock->expects($this->once())->method('sendVary');

        $this->httpPlugin->beforeSendResponse($responseMock);
    }

    /**
     * @return array
     */
    public static function nonPublicCacheControlDataProvider(): array
    {
        return [
            'no_cache_control_header' => [null],
            'no_store' => ['no-store, no-cache, must-revalidate, max-age=0'],
            'private' => ['max-age=60, private'],
            'public_without_shared_ttl' => ['public, max-age=60'],
        ];
    }
}
