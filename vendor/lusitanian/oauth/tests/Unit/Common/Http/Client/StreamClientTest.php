<?php

namespace OAuthTest\Unit\Common\Http\Client;

use OAuth\Common\Http\Client\StreamClient;

class StreamClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testConstructCorrectInstance()
    {
        $client = new StreamClient();

        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Client\\AbstractClient', $client);
    }

    /**
     * @covers OAuth\Common\Http\Client\StreamClient::retrieveResponse
     */
    public function testRetrieveResponseThrowsExceptionOnGetRequestWithBody()
    {
        $this->setExpectedException('\\InvalidArgumentException');

        $client = new StreamClient();

        $client->retrieveResponse(
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface'),
            'foo',
            array(),
            'GET'
        );
    }

    /**
     * @covers OAuth\Common\Http\Client\StreamClient::retrieveResponse
     */
    public function testRetrieveResponseThrowsExceptionOnGetRequestWithBodyMethodConvertedToUpper()
    {
        $this->setExpectedException('\\InvalidArgumentException');

        $client = new StreamClient();

        $client->retrieveResponse(
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface'),
            'foo',
            array(),
            'get'
        );
    }

    /**
     * @covers OAuth\Common\Http\Client\StreamClient::retrieveResponse
     * @covers OAuth\Common\Http\Client\StreamClient::generateStreamContext
     */
    public function testRetrieveResponseDefaultUserAgent()
    {
        $endPoint = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $endPoint->expects($this->any())
            ->method('getHost')
            ->will($this->returnValue('httpbin.org'));
        $endPoint->expects($this->any())
            ->method('getAbsoluteUri')
            ->will($this->returnValue('http://httpbin.org/get'));

        $client = new StreamClient();

        $response = $client->retrieveResponse(
            $endPoint,
            '',
            array(),
            'get'
        );

        $response = json_decode($response, true);

        $this->assertSame('PHPoAuthLib', $response['headers']['User-Agent']);
    }

    /**
     * @covers OAuth\Common\Http\Client\StreamClient::retrieveResponse
     * @covers OAuth\Common\Http\Client\StreamClient::generateStreamContext
     */
    public function testRetrieveResponseCustomUserAgent()
    {
        $endPoint = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $endPoint->expects($this->any())
            ->method('getHost')
            ->will($this->returnValue('httpbin.org'));
        $endPoint->expects($this->any())
            ->method('getAbsoluteUri')
            ->will($this->returnValue('http://httpbin.org/get'));

        $client = new StreamClient('My Super Awesome Http Client');

        $response = $client->retrieveResponse(
            $endPoint,
            '',
            array(),
            'get'
        );

        $response = json_decode($response, true);

        $this->assertSame('My Super Awesome Http Client', $response['headers']['User-Agent']);
    }

    /**
     * @covers OAuth\Common\Http\Client\StreamClient::retrieveResponse
     * @covers OAuth\Common\Http\Client\StreamClient::generateStreamContext
     */
    public function testRetrieveResponseWithCustomContentType()
    {
        $endPoint = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $endPoint->expects($this->any())
            ->method('getHost')
            ->will($this->returnValue('httpbin.org'));
        $endPoint->expects($this->any())
            ->method('getAbsoluteUri')
            ->will($this->returnValue('http://httpbin.org/get'));

        $client = new StreamClient();

        $response = $client->retrieveResponse(
            $endPoint,
            '',
            array('Content-Type' => 'foo/bar'),
            'get'
        );

        $response = json_decode($response, true);

        $this->assertSame('foo/bar', $response['headers']['Content-Type']);
    }

    /**
     * @covers OAuth\Common\Http\Client\StreamClient::retrieveResponse
     * @covers OAuth\Common\Http\Client\StreamClient::generateStreamContext
     */
    public function testRetrieveResponseWithFormUrlEncodedContentType()
    {
        $endPoint = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $endPoint->expects($this->any())
            ->method('getHost')
            ->will($this->returnValue('httpbin.org'));
        $endPoint->expects($this->any())
            ->method('getAbsoluteUri')
            ->will($this->returnValue('http://httpbin.org/post'));

        $client = new StreamClient();

        $response = $client->retrieveResponse(
            $endPoint,
            array('foo' => 'bar', 'baz' => 'fab'),
            array(),
            'POST'
        );

        $response = json_decode($response, true);

        $this->assertSame('application/x-www-form-urlencoded', $response['headers']['Content-Type']);
        $this->assertEquals(array('foo' => 'bar', 'baz' => 'fab'), $response['form']);
    }

    /**
     * @covers OAuth\Common\Http\Client\StreamClient::retrieveResponse
     * @covers OAuth\Common\Http\Client\StreamClient::generateStreamContext
     */
    public function testRetrieveResponseHost()
    {
        $endPoint = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $endPoint->expects($this->any())
            ->method('getHost')
            ->will($this->returnValue('httpbin.org'));
        $endPoint->expects($this->any())
            ->method('getAbsoluteUri')
            ->will($this->returnValue('http://httpbin.org/post'));

        $client = new StreamClient();

        $response = $client->retrieveResponse(
            $endPoint,
            array('foo' => 'bar', 'baz' => 'fab'),
            array(),
            'POST'
        );

        $response = json_decode($response, true);

        $this->assertSame('httpbin.org', $response['headers']['Host']);
    }

    /**
     * @covers OAuth\Common\Http\Client\StreamClient::retrieveResponse
     * @covers OAuth\Common\Http\Client\StreamClient::generateStreamContext
     */
    public function testRetrieveResponsePostRequestWithRequestBodyAsString()
    {
        $endPoint = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $endPoint->expects($this->any())
            ->method('getHost')
            ->will($this->returnValue('httpbin.org'));
        $endPoint->expects($this->any())
            ->method('getAbsoluteUri')
            ->will($this->returnValue('http://httpbin.org/post'));

        $formData = array('baz' => 'fab', 'foo' => 'bar');

        $client = new StreamClient();

        $response = $client->retrieveResponse(
            $endPoint,
            $formData,
            array(),
            'POST'
        );

        $response = json_decode($response, true);

        $this->assertSame($formData, $response['form']);
    }

    /**
     * @covers OAuth\Common\Http\Client\StreamClient::retrieveResponse
     * @covers OAuth\Common\Http\Client\StreamClient::generateStreamContext
     */
    public function testRetrieveResponsePutRequestWithRequestBodyAsString()
    {
        $endPoint = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $endPoint->expects($this->any())
            ->method('getHost')
            ->will($this->returnValue('httpbin.org'));
        $endPoint->expects($this->any())
            ->method('getAbsoluteUri')
            ->will($this->returnValue('http://httpbin.org/put'));

        $formData = array('baz' => 'fab', 'foo' => 'bar');

        $client = new StreamClient();

        $response = $client->retrieveResponse(
            $endPoint,
            $formData,
            array(),
            'PUT'
        );

        $response = json_decode($response, true);

        $this->assertSame($formData, $response['form']);
    }

    /**
     * @covers OAuth\Common\Http\Client\StreamClient::retrieveResponse
     * @covers OAuth\Common\Http\Client\StreamClient::generateStreamContext
     */
    public function testRetrieveResponseThrowsExceptionOnInvalidRequest()
    {
        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $endPoint = $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface');
        $endPoint->expects($this->any())
            ->method('getHost')
            ->will($this->returnValue('dskjhfckjhekrsfhkehfkreljfrekljfkre'));
        $endPoint->expects($this->any())
            ->method('getAbsoluteUri')
            ->will($this->returnValue('dskjhfckjhekrsfhkehfkreljfrekljfkre'));

        $client = new StreamClient();

        $response = $client->retrieveResponse(
            $endPoint,
            '',
            array('Content-Type' => 'foo/bar'),
            'get'
        );

        $response = json_decode($response, true);

        $this->assertSame('foo/bar', $response['headers']['Content-Type']);
    }
}
