<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\PhpEnvironment;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\HTTP\PhpEnvironment\Response */
    protected $response;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Zend\Http\Headers */
    protected $headers;

    protected function setUp()
    {
        $this->response = $this->getMock(
            'Magento\Framework\HTTP\PhpEnvironment\Response',
            ['getHeaders', 'send', 'isException', 'renderExceptions', 'getException', 'clearHeader']
        );
        $this->headers = $this->getMock(
            'Zend\Http\Headers',
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
        $this->response
            ->expects($this->once())
            ->method('isException')
            ->will($this->returnValue(true));
        $this->response
            ->expects($this->once())
            ->method('renderExceptions')
            ->will($this->returnValue(true));
        $this->response
            ->expects($this->once())
            ->method('getException')
            ->will($this->returnValue([new \Exception('Test exception method')]));

        $this->assertNull($this->response->sendResponse());
    }

    public function testSendResponse()
    {
        $this->response
            ->expects($this->once())
            ->method('isException')
            ->will($this->returnValue(false));
        $this->response
            ->expects($this->never())
            ->method('renderException')
            ->will($this->returnValue(false));
        $this->response
            ->expects($this->never())
            ->method('getException');
        $this->response
            ->expects($this->once())
            ->method('send');

        $this->response->sendResponse();
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
            'Magento\Framework\HTTP\PhpEnvironment\Response',
            ['getHeaders', 'send', 'isException', 'renderExceptions', 'getException']
        );

        $this->headers->addHeaderLine('Header-name: header-value');

        $header = $this->getMock(
            'Zend\Http\Header\GenericHeader',
            ['getFieldName'],
            ['Header-name', 'header-value']
        );
        $header
            ->expects($this->once())
            ->method('getFieldName')
            ->will($this->returnValue('Header-name'));

        $this->headers
            ->expects($this->once())
            ->method('has')
            ->with('Header-name')
            ->will($this->returnValue(true));
        $this->headers
            ->expects($this->once())
            ->method('current')
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

    public function testClearHeaderIfHeaderExistsAndWasNotFound()
    {
        $response = $this->response = $this->getMock(
            'Magento\Framework\HTTP\PhpEnvironment\Response',
            ['getHeaders', 'send', 'isException', 'renderExceptions', 'getException']
        );

        $this->headers->addHeaderLine('Header-name: header-value');

        $header = $this->getMock(
            'Zend\Http\Header\GenericHeader',
            ['getFieldName'],
            ['Header-name', 'header-value']
        );
        $header
            ->expects($this->once())
            ->method('getFieldName')
            ->will($this->returnValue('Wrong-header-name'));

        $this->headers
            ->expects($this->once())
            ->method('has')
            ->with('Header-name')
            ->will($this->returnValue(true));
        $this->headers
            ->expects($this->once())
            ->method('current')
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

    public function testClearHeaderAndHeaderNotExists()
    {
        $response = $this->response = $this->getMock(
            'Magento\Framework\HTTP\PhpEnvironment\Response',
            ['getHeaders', 'send', 'isException', 'renderExceptions', 'getException']
        );

        $this->headers->addHeaderLine('Header-name: header-value');

        $header = $this->getMock(
            'Zend\Http\Header\GenericHeader',
            ['getFieldName'],
            ['Header-name', 'header-value']
        );
        $header
            ->expects($this->never())
            ->method('getFieldName')
            ->will($this->returnValue('Wrong-header-name'));

        $this->headers
            ->expects($this->once())
            ->method('has')
            ->with('Header-name')
            ->will($this->returnValue(false));
        $this->headers
            ->expects($this->never())
            ->method('current')
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

    public function testRenderExceptions()
    {
        $response = new Response();
        $this->assertTrue($response->renderExceptions(true));
    }

    public function testHasExceptionOfType()
    {
        $this->response->setException(new \Exception());
        $hasException = $this->response->hasExceptionOfType('Exception');
        $this->assertTrue($hasException);
    }
    public function testHasExceptionOfTypeIfExceptionsIsEmpty()
    {
        $this->response->setException(new \Exception());
        $hasException = $this->response->hasExceptionOfType('Test\Exception');
        $this->assertFalse($hasException);
    }

    public function testHasExceptionOfMessage()
    {
        $this->response->setException(new \Exception('Test message'));
        $hasException = $this->response->hasExceptionOfMessage('Test message');
        $this->assertTrue($hasException);
    }
    public function testHasExceptionOfMessageIfExceptionMessageNotFound()
    {
        $this->response->setException(new \Exception('Test message'));
        $hasException = $this->response->hasExceptionOfMessage('Not found');
        $this->assertFalse($hasException);
    }

    public function testHasExceptionOfCode()
    {
        $this->response->setException(new \Exception('Test message', 234));
        $hasException = $this->response->hasExceptionOfCode(234);
        $this->assertTrue($hasException);
    }
    public function testHasExceptionOfCodeIfExceptionWithCodeNotFound()
    {
        $this->response->setException(new \Exception('Test message', 234));
        $hasException = $this->response->hasExceptionOfCode(457);
        $this->assertFalse($hasException);
    }

    public function testGetExceptionByType()
    {
        $this->response->setException(new \Exception('Test message', 234));
        $exceptions = $this->response->getExceptionByType('Exception');
        $this->assertArrayHasKey(0, $exceptions);
        $this->assertInstanceOf('Exception', $exceptions[0]);
    }

    public function testGetExceptionByTypeIfRequiredExceptionNotExists()
    {
        $this->response->setException(new \Exception('Test message', 234));
        $exceptions = $this->response->getExceptionByType('Test\Exception');
        $this->assertFalse($exceptions);
    }

    public function testGetExceptionByMessage()
    {
        $this->response->setException(new \Exception('Test message', 234));
        $exceptions = $this->response->getExceptionByMessage('Test message');
        $this->assertArrayHasKey(0, $exceptions);
        $this->assertEquals('Test message', $exceptions[0]->getMessage());
    }

    public function testGetExceptionByMessageIfRequiredExceptionNotExists()
    {
        $this->response->setException(new \Exception('Test message', 234));
        $exceptions = $this->response->getExceptionByMessage('test test');
        $this->assertFalse($exceptions);
    }

    public function testGetExceptionByCode()
    {
        $this->response->setException(new \Exception('Test message', 234));
        $exceptions = $this->response->getExceptionByCode(234);
        $this->assertArrayHasKey(0, $exceptions);
        $this->assertEquals(234, $exceptions[0]->getCode());
    }

    public function testGetExceptionByCodeIfRequiredExceptionNotExists()
    {
        $this->response->setException(new \Exception('Test message', 234));
        $exceptions = $this->response->getExceptionByCode(486);
        $this->assertFalse($exceptions);
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
            'Magento\Framework\HTTP\PhpEnvironment\Response',
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
        $response
            ->expects($this->once())
            ->method('sendHeaders')
            ->will($this->returnSelf());

        $response->setRedirect('testUrl');
    }
}
