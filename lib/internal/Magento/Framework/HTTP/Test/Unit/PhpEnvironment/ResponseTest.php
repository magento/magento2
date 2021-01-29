<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\Test\Unit\PhpEnvironment;

use \Magento\Framework\HTTP\PhpEnvironment\Response;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\HTTP\PhpEnvironment\Response */
    protected $response;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Zend\Http\Headers */
    protected $headers;

    protected function setUp(): void
    {
        $this->response = $this->createPartialMock(
            \Magento\Framework\HTTP\PhpEnvironment\Response::class,
            ['getHeaders', 'send', 'clearHeader']
        );
        $this->headers = $this->createPartialMock(
            \Zend\Http\Headers::class,
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
            \Magento\Framework\HTTP\PhpEnvironment\Response::class,
            ['getHeaders', 'send']
        );

        $this->headers->addHeaderLine('Header-name: header-value');

        $header = \Zend\Http\Header\GenericHeader::fromString('Header-name: header-value');

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
            \Magento\Framework\HTTP\PhpEnvironment\Response::class,
            ['getHeaders', 'send']
        );

        $this->headers->addHeaderLine('Header-name: header-value');

        $header = \Zend\Http\Header\GenericHeader::fromString('Header-name: header-value');

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

    /**
     */
    public function testHttpResponseCodeWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
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
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->createPartialMock(
            \Magento\Framework\HTTP\PhpEnvironment\Response::class,
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
