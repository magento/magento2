<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\Test\Unit\PhpEnvironment;

use \Magento\Framework\HTTP\PhpEnvironment\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\HTTP\PhpEnvironment\Response */
    protected $response;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Zend\Http\Headers */
    protected $headers;

    protected function setUp()
    {
        $this->response = $this->getMock(
            \Magento\Framework\HTTP\PhpEnvironment\Response::class,
            ['getHeaders', 'send', 'clearHeader']
        );
        $this->headers = $this->getMock(
            \Zend\Http\Headers::class,
            ['has', 'get', 'current', 'removeHeader']
        );
    }

    protected function tearDown()
    {
        unset($this->response);
    }

    public function testGetHeader()
    {
        $this->response
            ->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($this->headers));
        $this->headers
            ->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));
        $this->headers
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(true));

        $this->assertTrue($this->response->getHeader('testName'));
    }

    public function testGetHeaderWithoutHeader()
    {
        $this->response
            ->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($this->headers));
        $this->headers
            ->expects($this->once())
            ->method('has')
            ->will($this->returnValue(false));
        $this->headers
            ->expects($this->never())
            ->method('get')
            ->will($this->returnValue(false));

        $this->assertFalse($this->response->getHeader('testName'));
    }

    public function testAppendBody()
    {
        $response = new Response();
        $response->appendBody('testContent');
        $this->assertContains('testContent', $response->getBody());
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
            ->will($this->returnValue($this->headers));
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
            ->will($this->returnValue($this->headers));
        $this->response
            ->expects($this->once())
            ->method('clearHeader')
            ->with('testName');

        $this->response->setHeader('testName', 'testValue', true);
    }

    public function testClearHeaderIfHeaderExistsAndWasFound()
    {
        $response = $this->response = $this->getMock(
            \Magento\Framework\HTTP\PhpEnvironment\Response::class,
            ['getHeaders', 'send']
        );

        $this->headers->addHeaderLine('Header-name: header-value');

        $header = \Zend\Http\Header\GenericHeader::fromString('Header-name: header-value');

        $this->headers
            ->expects($this->once())
            ->method('has')
            ->with('Header-name')
            ->will($this->returnValue(true));
        $this->headers
            ->expects($this->once())
            ->method('get')
            ->with('Header-name')
            ->will($this->returnValue($header));
        $this->headers
            ->expects($this->once())
            ->method('removeHeader')
            ->with($header)
            ->will($this->returnValue(true));

        $response
            ->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($this->headers));

        $response->clearHeader('Header-name');
    }

    public function testClearHeaderAndHeaderNotExists()
    {
        $response = $this->response = $this->getMock(
            \Magento\Framework\HTTP\PhpEnvironment\Response::class,
            ['getHeaders', 'send']
        );

        $this->headers->addHeaderLine('Header-name: header-value');

        $header = \Zend\Http\Header\GenericHeader::fromString('Header-name: header-value');

        $this->headers
            ->expects($this->once())
            ->method('has')
            ->with('Header-name')
            ->will($this->returnValue(false));
        $this->headers
            ->expects($this->never())
            ->method('get')
            ->with('Header-name')
            ->will($this->returnValue($header));
        $this->headers
            ->expects($this->never())
            ->method('removeHeader')
            ->with($header);

        $response
            ->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($this->headers));

        $response->clearHeader('Header-name');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp Invalid HTTP response code
     */
    public function testHttpResponseCodeWithException()
    {
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
        $response = $this->getMock(
            \Magento\Framework\HTTP\PhpEnvironment\Response::class,
            ['setHeader', 'setHttpResponseCode', 'sendHeaders'],
            [],
            '',
            false
        );
        $response
            ->expects($this->once())
            ->method('setHeader')
            ->with('Location', 'testUrl', true)
            ->will($this->returnSelf());
        $response
            ->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(302)
            ->will($this->returnSelf());

        $response->setRedirect('testUrl');
    }
}
