<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\Test\Unit\AsyncClient;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Magento\Framework\HTTP\AsyncClient\GuzzleWrapDeferred;
use Magento\Framework\HTTP\AsyncClient\HttpException;
use PHPUnit\Framework\TestCase;

class GuzzleWrapDeferredTest extends TestCase
{
    public function testGetWrapsConnectExceptionInHttpException(): void
    {
        $connectException = new ConnectException(
            'Connection timed out',
            new Request('GET', 'http://localhost')
        );
        $promise = $this->createMock(PromiseInterface::class);
        $promise->method('wait')->willThrowException($connectException);

        $deferred = new GuzzleWrapDeferred($promise);

        try {
            $deferred->get();
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $exception) {
            $this->assertSame($connectException, $exception->getPrevious());
        }
    }

    public function testGetConvertsBadResponseExceptionToResponse(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->method('wait')->willThrowException(
            new ServerException(
                'Server error',
                new Request('GET', 'http://localhost'),
                new Response(503, ['Retry-After' => '60'], 'Service unavailable')
            )
        );

        $deferred = new GuzzleWrapDeferred($promise);

        $response = $deferred->get();

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame(['retry-after' => '60'], $response->getHeaders());
        $this->assertSame('Service unavailable', $response->getBody());
    }
}
