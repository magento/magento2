<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\HTTP\Test\Unit\PhpEnvironment;

use Laminas\Http\Header\GenericHeader;
use Laminas\Http\Headers;
use Magento\Framework\App\Response\Http;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /** @var MockObject|Response */
    protected $response;

    /** @var MockObject|Headers */
    protected $headers;

    protected function setUp(): void
    {
        $this->response = $this->createPartialMock(
            Response::class,
            ['getHeaders', 'send', 'clearHeader']
        );
        $this->headers = $this->createPartialMock(
            Headers::class,
            ['has', 'get', 'current', 'removeHeader']
        );
    }

    protected function tearDown(): void
    {
        unset($this->response);
    }

    public function testGetHeader()
    {
        $this->response
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn($this->headers);
        $this->headers
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $this->headers
            ->expects($this->once())
            ->method('get')
            ->willReturn(true);

        $this->assertTrue($this->response->getHeader('testName'));
    }

    public function testGetHeaderWithoutHeader()
    {
        $this->response
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn($this->headers);
        $this->headers
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);
        $this->headers
            ->expects($this->never())
            ->method('get')
            ->willReturn(false);

        $this->assertFalse($this->response->getHeader('testName'));
    }

    public function testAppendBody()
    {
        $response = new Response();
        $response->appendBody('testContent');
        $this->assertStringContainsString('testContent', $response->getBody());
    }

    public function testSendResponseWithException()
    {
        $this->assertNull($this->response->sendResponse());
    }

    public function testSetHeaderWithoutReplacing()
    {
        $this->response
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn($this->headers);
        $this->response
            ->expects($this->never())
            ->method('clearHeader')
            ->with('testName');

        $this->response->setHeader('testName', 'testValue');
    }

    public function testSetHeaderWithReplacing()
    {
        $this->response
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn($this->headers);
        $this->response
            ->expects($this->once())
            ->method('clearHeader')
            ->with('testName');

        $this->response->setHeader('testName', 'testValue', true);
    }

    public function testClearHeaderIfHeaderExistsAndWasFound()
    {
        $response = $this->response = $this->createPartialMock(
            Response::class,
            ['getHeaders', 'send']
        );

        $this->headers->addHeaderLine('Header-name: header-value');

        $header = GenericHeader::fromString('Header-name: header-value');

        $this->headers
            ->expects($this->once())
            ->method('has')
            ->with('Header-name')
            ->willReturn(true);
        $this->headers
            ->expects($this->once())
            ->method('get')
            ->with('Header-name')
            ->willReturn($header);
        $this->headers
            ->expects($this->once())
            ->method('removeHeader')
            ->with($header)
            ->willReturn(true);

        $response
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn($this->headers);

        $response->clearHeader('Header-name');
    }

    public function testClearHeaderAndHeaderNotExists()
    {
        $response = $this->response = $this->createPartialMock(
            Response::class,
            ['getHeaders', 'send']
        );

        $this->headers->addHeaderLine('Header-name: header-value');

        $header = GenericHeader::fromString('Header-name: header-value');

        $this->headers
            ->expects($this->once())
            ->method('has')
            ->with('Header-name')
            ->willReturn(false);
        $this->headers
            ->expects($this->never())
            ->method('get')
            ->with('Header-name')
            ->willReturn($header);
        $this->headers
            ->expects($this->never())
            ->method('removeHeader')
            ->with($header);

        $response
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn($this->headers);

        $response->clearHeader('Header-name');
    }

    public function testHttpResponseCodeWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessageMatches('/Invalid HTTP response code/');
        $this->response->setHttpResponseCode(1);
    }

    /**
     * Test for setRedirect method.
     *
     * @covers \Magento\Framework\HTTP\PhpEnvironment\Response::setRedirect
     */
    public function testSetRedirect()
    {
        /** @var Http $response */
        $response = $this->createPartialMock(
            Response::class,
            ['setHeader', 'setHttpResponseCode', 'sendHeaders']
        );
        $response
            ->expects($this->once())
            ->method('setHeader')
            ->with('Location', 'testUrl', true)
            ->willReturnSelf();
        $response
            ->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(302)
            ->willReturnSelf();

        $response->setRedirect('testUrl');
    }
}
