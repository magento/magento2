<?php

namespace OAuthTest\Unit\OAuth1\Service;

use OAuth\OAuth1\Service\Twitter;

class TwitterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     */
    public function testConstructCorrectInterfaceWithoutCustomUri()
    {
        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Service\\ServiceInterface', $service);
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     */
    public function testConstructCorrectInstanceWithoutCustomUri()
    {
        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     */
    public function testConstructCorrectInstanceWithCustomUri()
    {
        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     * @covers OAuth\OAuth1\Service\Twitter::getRequestTokenEndpoint
     */
    public function testGetRequestTokenEndpoint()
    {
        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertSame(
            'https://api.twitter.com/oauth/request_token',
            $service->getRequestTokenEndpoint()->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     * @covers OAuth\OAuth1\Service\Twitter::getAuthorizationEndpoint
     */
    public function testGetAuthorizationEndpoint()
    {
        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertTrue(
            in_array(
                strtolower($service->getAuthorizationEndpoint()->getAbsoluteUri()), 
                array(\OAuth\OAuth1\Service\Twitter::ENDPOINT_AUTHENTICATE, \OAuth\OAuth1\Service\Twitter::ENDPOINT_AUTHORIZE)
            )
        );

        $service->setAuthorizationEndpoint( \OAuth\OAuth1\Service\Twitter::ENDPOINT_AUTHORIZE );

        $this->assertTrue(
            in_array(
                strtolower($service->getAuthorizationEndpoint()->getAbsoluteUri()), 
                array(\OAuth\OAuth1\Service\Twitter::ENDPOINT_AUTHENTICATE, \OAuth\OAuth1\Service\Twitter::ENDPOINT_AUTHORIZE)
            )
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     * @covers OAuth\OAuth1\Service\Twitter::setAuthorizationEndpoint
     */
    public function testSetAuthorizationEndpoint()
    {
        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Exception\\Exception');

        $service->setAuthorizationEndpoint('foo');
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     * @covers OAuth\OAuth1\Service\Twitter::getAccessTokenEndpoint
     */
    public function testGetAccessTokenEndpoint()
    {
        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertSame(
            'https://api.twitter.com/oauth/access_token',
            $service->getAccessTokenEndpoint()->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     * @covers OAuth\OAuth1\Service\Twitter::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Twitter::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnNulledResponse()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue(null));

        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     * @covers OAuth\OAuth1\Service\Twitter::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Twitter::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseNotAnArray()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('notanarray'));

        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     * @covers OAuth\OAuth1\Service\Twitter::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Twitter::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseCallbackNotSet()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('foo=bar'));

        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     * @covers OAuth\OAuth1\Service\Twitter::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Twitter::parseRequestTokenResponse
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseCallbackNotTrue()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue(
            'oauth_callback_confirmed=false'
        ));

        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestRequestToken();
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     * @covers OAuth\OAuth1\Service\Twitter::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Twitter::parseRequestTokenResponse
     * @covers OAuth\OAuth1\Service\Twitter::parseAccessTokenResponse
     */
    public function testParseRequestTokenResponseValid()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue(
            'oauth_callback_confirmed=true&oauth_token=foo&oauth_token_secret=bar'
        ));

        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Token\\StdOAuth1Token', $service->requestRequestToken());
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     * @covers OAuth\OAuth1\Service\Twitter::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Twitter::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseThrowsExceptionOnError()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('error=bar'));

        $token = $this->getMock('\\OAuth\\OAuth1\\Token\\TokenInterface');

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->any())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage,
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestAccessToken('foo', 'bar', $token);
    }

    /**
     * @covers OAuth\OAuth1\Service\Twitter::__construct
     * @covers OAuth\OAuth1\Service\Twitter::getRequestTokenEndpoint
     * @covers OAuth\OAuth1\Service\Twitter::parseAccessTokenResponse
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

        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage,
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Token\\StdOAuth1Token', $service->requestAccessToken('foo', 'bar', $token));
    }
    
    /**
     * @covers OAuth\OAuth1\Service\Twitter::parseAccessTokenResponse
     */
    public function testParseAccessTokenErrorTotalBullshit()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );
        
        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');
        $method = new \ReflectionMethod(get_class($service), 'parseAccessTokenResponse');
        $method->setAccessible(true);
        $method->invokeArgs($service, array("hoho"));
    }
    
    /**
     * @covers OAuth\OAuth1\Service\Twitter::parseAccessTokenResponse
     */
    public function testParseAccessTokenErrorItsAnError()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );
        
        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');
        $method = new \ReflectionMethod(get_class($service), 'parseAccessTokenResponse');
        $method->setAccessible(true);
        $method->invokeArgs($service, array("error=hihihaha"));
    }
    
    /**
     * @covers OAuth\OAuth1\Service\Twitter::parseAccessTokenResponse
     */
    public function testParseAccessTokenErrorItsMissingOauthToken()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );
        
        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');
        $method = new \ReflectionMethod(get_class($service), 'parseAccessTokenResponse');
        $method->setAccessible(true);
        $method->invokeArgs($service, array("oauth_token_secret=1"));
    }
    
    /**
     * @covers OAuth\OAuth1\Service\Twitter::parseAccessTokenResponse
     */
    public function testParseAccessTokenErrorItsMissingOauthTokenSecret()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $service = new Twitter(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );
        
        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');
        $method = new \ReflectionMethod(get_class($service), 'parseAccessTokenResponse');
        $method->setAccessible(true);
        $method->invokeArgs($service, array("oauth_token=1"));
    }
}
