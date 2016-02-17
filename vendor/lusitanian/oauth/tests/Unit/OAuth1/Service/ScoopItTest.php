<?php

namespace OAuthTest\Unit\OAuth1\Service;

use OAuth\OAuth1\Service\ScoopIt;

class ScoopItTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     */
    public function testConstructCorrectInterfaceWithoutCustomUri()
    {
        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Service\\ServiceInterface', $service);
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     */
    public function testConstructCorrectInstanceWithoutCustomUri()
    {
        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     */
    public function testConstructCorrectInstanceWithCustomUri()
    {
        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     * @covers OAuth\OAuth1\Service\ScoopIt::getRequestTokenEndpoint
     */
    public function testGetRequestTokenEndpoint()
    {
        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertSame(
            'https://www.scoop.it/oauth/request',
            $service->getRequestTokenEndpoint()->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     * @covers OAuth\OAuth1\Service\ScoopIt::getAuthorizationEndpoint
     */
    public function testGetAuthorizationEndpoint()
    {
        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertSame(
            'https://www.scoop.it/oauth/authorize',
            $service->getAuthorizationEndpoint()->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     * @covers OAuth\OAuth1\Service\ScoopIt::getAccessTokenEndpoint
     */
    public function testGetAccessTokenEndpoint()
    {
        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertSame(
            'https://www.scoop.it/oauth/access',
            $service->getAccessTokenEndpoint()->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     * @covers OAuth\OAuth1\Service\ScoopIt::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\ScoopIt::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnNulledResponse()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue(null));

        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     * @covers OAuth\OAuth1\Service\ScoopIt::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\ScoopIt::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseNotAnArray()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('notanarray'));

        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     * @covers OAuth\OAuth1\Service\ScoopIt::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\ScoopIt::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseCallbackNotSet()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('foo=bar'));

        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     * @covers OAuth\OAuth1\Service\ScoopIt::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\ScoopIt::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseCallbackNotTrue()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue(
            'oauth_callback_confirmed=false'
        ));

        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     * @covers OAuth\OAuth1\Service\ScoopIt::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\ScoopIt::parseRequestTokenResponse
     * @covers OAuth\OAuth1\Service\ScoopIt::parseAccessTokenResponse
     */
    public function testParseRequestTokenResponseValid()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue(
            'oauth_callback_confirmed=true&oauth_token=foo&oauth_token_secret=bar'
        ));

        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Token\\StdOAuth1Token', $service->requestRequestToken());
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     * @covers OAuth\OAuth1\Service\ScoopIt::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\ScoopIt::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseThrowsExceptionOnError()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('error=bar'));

        $token = $this->getMock('\\OAuth\\OAuth1\\Token\\TokenInterface');

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->any())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage,
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestAccessToken('foo', 'bar', $token);
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::__construct
     * @covers OAuth\OAuth1\Service\ScoopIt::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\ScoopIt::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseValid()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue(
            'oauth_token=foo&oauth_token_secret=bar'
        ));

        $token = $this->getMock('\\OAuth\\OAuth1\\Token\\TokenInterface');

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->any())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage,
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Token\\StdOAuth1Token', $service->requestAccessToken('foo', 'bar', $token));
    }

    /**
     * @covers OAuth\OAuth1\Service\ScoopIt::request
     */
    public function testRequest()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('response!'));

        $token = $this->getMock('\\OAuth\\OAuth1\\Token\\TokenInterface');

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->any())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new ScoopIt(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage,
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertSame('response!', $service->request('/my/awesome/path'));
    }
}
