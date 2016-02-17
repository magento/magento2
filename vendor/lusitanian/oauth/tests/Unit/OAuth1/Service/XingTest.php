<?php

namespace OAuthTest\Unit\OAuth1\Service;

use OAuth\OAuth1\Service\Xing;

class XingTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $storage;
    private $xing;


    protected function setUp()
    {
        parent::setUp();

        $this->client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $this->storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');

        $this->xing = new Xing(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->client,
            $this->storage,
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     */
    public function testConstructCorrectInterfaceWithoutCustomUri()
    {
        $this->assertInstanceOf(
            '\\OAuth\\OAuth1\\Service\\ServiceInterface', $this->xing
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     */
    public function testConstructCorrectInstanceWithoutCustomUri()
    {
        $this->assertInstanceOf(
            '\\OAuth\\OAuth1\\Service\\AbstractService', $this->xing
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     */
    public function testConstructCorrectInstanceWithCustomUri()
    {
        $service = new Xing(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->client,
            $this->storage,
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     * @covers OAuth\OAuth1\Service\Xing::getRequestTokenEndpoint
     */
    public function testGetRequestTokenEndpoint()
    {
        $this->assertSame(
            'https://api.xing.com/v1/request_token',
            $this->xing->getRequestTokenEndpoint()->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     * @covers OAuth\OAuth1\Service\Xing::getAuthorizationEndpoint
     */
    public function testGetAuthorizationEndpoint()
    {
        $this->assertSame(
            'https://api.xing.com/v1/authorize',
            $this->xing->getAuthorizationEndpoint()->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     * @covers OAuth\OAuth1\Service\Xing::getAccessTokenEndpoint
     */
    public function testGetAccessTokenEndpoint()
    {
        $this->assertSame(
            'https://api.xing.com/v1/access_token',
            $this->xing->getAccessTokenEndpoint()->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     * @covers OAuth\OAuth1\Service\Xing::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Xing::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnNulledResponse()
    {
        $this->client
            ->expects($this->once())
            ->method('retrieveResponse')
            ->will($this->returnValue(null));

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $this->xing->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     * @covers OAuth\OAuth1\Service\Xing::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Xing::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseNotAnArray()
    {
        $this->client
            ->expects($this->once())
            ->method('retrieveResponse')
            ->will($this->returnValue('notanarray'));

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $this->xing->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     * @covers OAuth\OAuth1\Service\Xing::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Xing::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseCallbackNotSet()
    {
        $this->client
            ->expects($this->once())
            ->method('retrieveResponse')
            ->will($this->returnValue('foo=bar'));

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $this->xing->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     * @covers OAuth\OAuth1\Service\Xing::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Xing::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseCallbackNotTrue()
    {
        $this->client
            ->expects($this->once())
            ->method('retrieveResponse')
            ->will($this->returnValue('oauth_callback_confirmed=false'));

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $this->xing->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     * @covers OAuth\OAuth1\Service\Xing::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Xing::parseRequestTokenResponse
     * @covers OAuth\OAuth1\Service\Xing::parseAccessTokenResponse
     */
    public function testParseRequestTokenResponseValid()
    {
        $this->client
            ->expects($this->once())
            ->method('retrieveResponse')
            ->will($this->returnValue(
                'oauth_callback_confirmed=true&oauth_token=foo&oauth_token_secret=bar'
            ));

        $this->assertInstanceOf(
            '\\OAuth\\OAuth1\\Token\\StdOAuth1Token',
            $this->xing->requestRequestToken()
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     * @covers OAuth\OAuth1\Service\Xing::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Xing::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseThrowsExceptionOnError()
    {
        $this->client
            ->expects($this->once())
            ->method('retrieveResponse')
            ->will($this->returnValue('{"message":"Invalid OAuth signature","error_name":"INVALID_OAUTH_SIGNATURE"}'));

        $token = $this->getMock('\\OAuth\\OAuth1\\Token\\TokenInterface');

        $this->storage
            ->expects($this->any())
            ->method('retrieveAccessToken')
            ->will($this->returnValue($token));

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $this->xing->requestAccessToken('foo', 'bar', $token);
    }

    /**
     * @covers OAuth\OAuth1\Service\Xing::__construct
     * @covers OAuth\OAuth1\Service\Xing::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Xing::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseValid()
    {
        $this->client
            ->expects($this->once())
            ->method('retrieveResponse')
            ->will($this->returnValue('oauth_token=foo&oauth_token_secret=bar'));

        $token = $this->getMock('\\OAuth\\OAuth1\\Token\\TokenInterface');

        $this->storage
            ->expects($this->any())
            ->method('retrieveAccessToken')
            ->will($this->returnValue($token));


        $this->assertInstanceOf(
            '\\OAuth\\OAuth1\\Token\\StdOAuth1Token',
            $this->xing->requestAccessToken('foo', 'bar', $token)
        );
    }
}
