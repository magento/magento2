<?php

namespace OAuthTest\Unit\OAuth1\Service;

use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth1\Service\QuickBooks;

class QuickBooksTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructCorrectInterfaceWithoutCustomUri()
    {
        $service = $this->getQuickBooks();
        $this->assertInstanceOf(
            '\\OAuth\\OAuth1\\Service\\ServiceInterface',
            $service
        );
    }

    public function testConstructCorrectInstanceWithoutCustomUri()
    {
        $service = $this->getQuickBooks();
        $this->assertInstanceOf(
            '\\OAuth\\OAuth1\\Service\\AbstractService',
            $service
        );
    }

    public function testConstructCorrectInstanceWithCustomUri()
    {
        $service = new QuickBooks(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertInstanceOf(
            '\\OAuth\\OAuth1\\Service\\AbstractService',
            $service
        );
    }

    public function testGetRequestTokenEndpoint()
    {
        $service = $this->getQuickBooks();
        $this->assertSame(
            'https://oauth.intuit.com/oauth/v1/get_request_token',
            $service->getRequestTokenEndpoint()->getAbsoluteUri()
        );
    }

    public function testGetAuthorizationEndpoint()
    {
        $service = $this->getQuickBooks();
        $this->assertSame(
            'https://appcenter.intuit.com/Connect/Begin',
            $service->getAuthorizationEndpoint()->getAbsoluteUri()
        );
    }

    public function testGetAccessTokenEndpoint()
    {
        $service = $this->getQuickBooks();
        $this->assertSame(
            'https://oauth.intuit.com/oauth/v1/get_access_token',
            $service->getAccessTokenEndpoint()->getAbsoluteUri()
        );
    }

    /**
     * @expectedException \OAuth\Common\Http\Exception\TokenResponseException
     * @expectedExceptionMessage Error in retrieving token.
     */
    public function testParseRequestTokenResponseThrowsExceptionOnNulledResponse()
    {
        $client = $this->getClientInterfaceMockThatReturns(null);
        $service = $this->getQuickBooks($client);
        $service->requestRequestToken();
    }

    /**
     * @expectedException \OAuth\Common\Http\Exception\TokenResponseException
     * @expectedExceptionMessage Error in retrieving token.
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseNotAnArray()
    {
        $client = $this->getClientInterfaceMockThatReturns('notanarray');
        $service = $this->getQuickBooks($client);
        $service->requestRequestToken();
    }

    /**
     * @expectedException \OAuth\Common\Http\Exception\TokenResponseException
     * @expectedExceptionMessage Error in retrieving token.
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseCallbackNotSet()
    {
        $client = $this->getClientInterfaceMockThatReturns('foo=bar');
        $service = $this->getQuickBooks($client);
        $service->requestRequestToken();
    }

    /**
     * @expectedException \OAuth\Common\Http\Exception\TokenResponseException
     * @expectedExceptionMessage Error in retrieving token.
     */
    public function testParseRequestTokenResponseThrowsExceptionOnResponseCallbackNotTrue()
    {
        $client = $this->getClientInterfaceMockThatReturns(
            'oauth_callback_confirmed=false'
        );
        $service = $this->getQuickBooks($client);
        $service->requestRequestToken();
    }

    public function testParseRequestTokenResponseValid()
    {
        $client = $this->getClientInterfaceMockThatReturns(
            'oauth_callback_confirmed=true&oauth_token=foo&oauth_token_secret=bar'
        );
        $service = $this->getQuickBooks($client);
        $this->assertInstanceOf(
            '\\OAuth\\OAuth1\\Token\\StdOAuth1Token',
            $service->requestRequestToken()
        );
    }

    /**
     * @expectedException \OAuth\Common\Http\Exception\TokenResponseException
     * @expectedExceptionMessage Error in retrieving token: "bar"
     */
    public function testParseAccessTokenResponseThrowsExceptionOnError()
    {
        $token = $this->getMock('\\OAuth\\OAuth1\\Token\\TokenInterface');
        $service = $this->getQuickBooksForRequestingAccessToken(
            $token,
            'error=bar'
        );

        $service->requestAccessToken('foo', 'bar', $token);
    }

    public function testParseAccessTokenResponseValid()
    {
        $token = $this->getMock('\\OAuth\\OAuth1\\Token\\TokenInterface');
        $service = $this->getQuickBooksForRequestingAccessToken(
            $token,
            'oauth_token=foo&oauth_token_secret=bar'
        );

        $this->assertInstanceOf(
            '\\OAuth\\OAuth1\\Token\\StdOAuth1Token',
            $service->requestAccessToken('foo', 'bar', $token)
        );
    }

    protected function getQuickBooks(
        ClientInterface $client = null,
        TokenStorageInterface $storage = null
    )
    {
        if (!$client) {
            $client = $this->getMock(
                '\\OAuth\\Common\\Http\\Client\\ClientInterface'
            );
        }

        if (!$storage) {
            $storage = $this->getMock(
                '\\OAuth\\Common\\Storage\\TokenStorageInterface'
            );
        }

        return new QuickBooks(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage,
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface')
        );
    }

    protected function getQuickBooksForRequestingAccessToken(
        TokenInterface $token,
        $response
    )
    {
        $client = $this->getClientInterfaceMockThatReturns($response);
        $storage = $this->getMock(
            '\\OAuth\\Common\\Storage\\TokenStorageInterface'
        );
        $storage->expects($this->any())
            ->method('retrieveAccessToken')
            ->will($this->returnValue($token));

        return $this->getQuickBooks($client, $storage);
    }

    protected function getClientInterfaceMockThatReturns($returnValue)
    {
        $client = $this->getMock(
            '\\OAuth\\Common\\Http\\Client\\ClientInterface'
        );
        $client->expects($this->once())
            ->method('retrieveResponse')
            ->will($this->returnValue($returnValue));

        return $client;
    }
}
