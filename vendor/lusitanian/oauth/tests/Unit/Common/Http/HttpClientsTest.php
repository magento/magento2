<?php

/**
 * @category   OAuth
 * @package    Tests
 * @author     David Desberg <david@daviddesberg.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace OAuth\Unit\Common\Http;

use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Http\Client;

class HttpClientsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var object|\OAuth\Common\Http\Client\ClientInterface[]
     */
    protected $clients;

    public function setUp()
    {
        $streamClient = new Client\StreamClient();
        $streamClient->setTimeout(3);

        $curlClient = new Client\CurlClient();
        $curlClient->setTimeout(3);

        $this->clients[] = $streamClient;
        $this->clients[] = $curlClient;
    }

    public function tearDown()
    {
        foreach ($this->clients as $client) {
            unset($client);
        }
    }

    /**
     * Test that extra headers are passed properly
     */
    public function testHeaders()
    {
        $testUri = new Uri('http://httpbin.org/get');

        $me = $this;
        $headerCb = function ($response) use ($me) {
            $data = json_decode($response, true);
            $me->assertEquals('extraheadertest', $data['headers']['Testingheader']);
        };

        $this->__doTestRetrieveResponse($testUri, array(), array('Testingheader' => 'extraheadertest'), 'GET', $headerCb);
    }

    /**
     * Tests that we get an exception for a >= 400 status code
     */
    public function testException()
    {
        // sending a post here should get us a 405 which should trigger an exception
        $testUri = new Uri('http://httpbin.org/delete');
        foreach ($this->clients as $client) {
            $this->setExpectedException('OAuth\Common\Http\Exception\TokenResponseException');
            $client->retrieveResponse($testUri, array('blah' => 'blih'));
        }
    }

    /**
     * Tests the DELETE method
     */
    public function testDelete()
    {
        $testUri = new Uri('http://httpbin.org/delete');

        $me = $this;
        $deleteTestCb = function ($response) use ($me) {
            $data = json_decode($response, true);
            $me->assertEquals('', $data['data']);
        };

        $this->__doTestRetrieveResponse($testUri, array(), array(), 'DELETE', $deleteTestCb);
    }

    /**
     * Tests the PUT method
     */
    public function testPut()
    {
        $testUri = new Uri('http://httpbin.org/put');

        $me = $this;
        $putTestCb = function ($response) use ($me) {
            // verify the put response
            $data = json_decode($response, true);
            $me->assertEquals(json_encode(array('testKey' => 'testValue')), $data['data']);
        };

        $this->__doTestRetrieveResponse($testUri, json_encode(array('testKey' => 'testValue')), array('Content-Type' => 'application/json'), 'PUT', $putTestCb);
    }

    /**
     * Tests the POST method
     */
    public function testPost()
    {
        // http test server
        $testUri = new Uri('http://httpbin.org/post');

        $me = $this;
        $postTestCb = function ($response) use ($me) {
            // verify the post response
            $data = json_decode($response, true);
            // note that we check this because the retrieveResponse wrapper function automatically adds a content-type
            // if there isn't one and it
            $me->assertEquals('testValue', $data['form']['testKey']);
        };

        $this->__doTestRetrieveResponse($testUri, array('testKey' => 'testValue'), array(), 'POST', $postTestCb);
    }

    /**
     * Expect exception when we try to send a GET request with a body
     */
    public function testInvalidGet()
    {
        $testUri =  new Uri('http://site.net');

        foreach ($this->clients as $client) {
            $this->setExpectedException('InvalidArgumentException');
            $client->retrieveResponse($testUri, array('blah' => 'blih'), array(), 'GET');
        }
    }

    /**
     * Tests the GET method
     */
    public function testGet()
    {
        // test uri
        $testUri = new Uri('http://httpbin.org/get?testKey=testValue');

        $me = $this;
        $getTestCb = function ($response) use ($me) {
            $data = json_decode($response, true);
            $me->assertEquals('testValue', $data['args']['testKey']);
        };

        $this->__doTestRetrieveResponse($testUri, array(), array(), 'GET', $getTestCb);
    }

    /**
     * Test on all HTTP clients.
     *
     * @param UriInterface $uri
     * @param array        $param
     * @param array        $header
     * @param string       $method
     * @param \Closure     $responseCallback
     */
    protected function __doTestRetrieveResponse(UriInterface $uri, $param, array $header, $method, $responseCallback)
    {
        foreach ($this->clients as $client) {
            $response = $client->retrieveResponse($uri, $param, $header, $method);
            $responseCallback($response, $client);
        }
    }
}
