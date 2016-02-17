<?php

namespace OAuthTest\Unit\OAuth2\Service;

use OAuth\OAuth2\Service\Dropbox;
use OAuth\Common\Token\TokenInterface;

class DropboxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     */
    public function testConstructCorrectInterfaceWithoutCustomUri()
    {
        $service = new Dropbox(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Service\\ServiceInterface', $service);
    }

    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     */
    public function testConstructCorrectInstanceWithoutCustomUri()
    {
        $service = new Dropbox(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     */
    public function testConstructCorrectInstanceWithCustomUri()
    {
        $service = new Dropbox(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            array(),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     * @covers OAuth\OAuth2\Service\Dropbox::getAuthorizationUri
     */
    public function testGetAuthorizationUriWithoutAdditionalParams()
    {
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->once())->method('getConsumerId')->will($this->returnValue('foo'));
        $credentials->expects($this->once())->method('getCallbackUrl')->will($this->returnValue('bar'));

        $service = new Dropbox(
            $credentials,
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertSame(
            'https://www.dropbox.com/1/oauth2/authorize?client_id=foo&redirect_uri=bar&response_type=code&scope=',
            $service->getAuthorizationUri()->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     * @covers OAuth\OAuth2\Service\Dropbox::getAuthorizationUri
     */
    public function testGetAuthorizationUriWithAdditionalParams()
    {
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->once())->method('getConsumerId')->will($this->returnValue('foo'));
        $credentials->expects($this->once())->method('getCallbackUrl')->will($this->returnValue('bar'));

        $service = new Dropbox(
            $credentials,
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertSame(
            'https://www.dropbox.com/1/oauth2/authorize?client_id=foo&redirect_uri=bar&response_type=code&scope=',
            $service->getAuthorizationUri()->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     * @covers OAuth\OAuth2\Service\Dropbox::getAuthorizationEndpoint
     */
    public function testGetAuthorizationEndpoint()
    {
        $service = new Dropbox(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertSame('https://www.dropbox.com/1/oauth2/authorize', $service->getAuthorizationEndpoint()->getAbsoluteUri());
    }

    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     * @covers OAuth\OAuth2\Service\Dropbox::getAccessTokenEndpoint
     */
    public function testGetAccessTokenEndpoint()
    {
        $service = new Dropbox(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertSame('https://api.dropbox.com/1/oauth2/token', $service->getAccessTokenEndpoint()->getAbsoluteUri());
    }

    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     * @covers OAuth\OAuth2\Service\Dropbox::getAuthorizationMethod
     */
    public function testGetAuthorizationMethod()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnArgument(0));

        $token = $this->getMock('\\OAuth\\OAuth2\\Token\\TokenInterface');
        $token->expects($this->once())->method('getEndOfLife')->will($this->returnValue(TokenInterface::EOL_NEVER_EXPIRES));
        $token->expects($this->once())->method('getAccessToken')->will($this->returnValue('foo'));

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->once())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new Dropbox(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage
        );

        $uri         = $service->request('https://pieterhordijk.com/my/awesome/path');
        $absoluteUri = parse_url($uri->getAbsoluteUri());

        $this->assertSame('access_token=foo', $absoluteUri['query']);
    }

    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     * @covers OAuth\OAuth2\Service\Dropbox::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseThrowsExceptionOnNulledResponse()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue(null));

        $service = new Dropbox(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestAccessToken('foo');
    }

    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     * @covers OAuth\OAuth2\Service\Dropbox::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseThrowsExceptionOnError()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('error=some_error'));

        $service = new Dropbox(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestAccessToken('foo');
    }

    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     * @covers OAuth\OAuth2\Service\Dropbox::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseValidWithoutRefreshToken()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('{"access_token":"foo","expires_in":"bar"}'));

        $service = new Dropbox(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Token\\StdOAuth2Token', $service->requestAccessToken('foo'));
    }

    /**
     * @covers OAuth\OAuth2\Service\Dropbox::__construct
     * @covers OAuth\OAuth2\Service\Dropbox::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseValidWithRefreshToken()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('{"access_token":"foo","expires_in":"bar","refresh_token":"baz"}'));

        $service = new Dropbox(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Token\\StdOAuth2Token', $service->requestAccessToken('foo'));
    }
}
