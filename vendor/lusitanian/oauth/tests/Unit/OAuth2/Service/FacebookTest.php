<?php

namespace OAuthTest\Unit\OAuth2\Service;

use OAuth\OAuth2\Service\Facebook;
use OAuth\Common\Token\TokenInterface;

class FacebookTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     */
    public function testConstructCorrectInterfaceWithoutCustomUri()
    {
        $service = new Facebook(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Service\\ServiceInterface', $service);
    }

    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     */
    public function testConstructCorrectInstanceWithoutCustomUri()
    {
        $service = new Facebook(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     */
    public function testConstructCorrectInstanceWithCustomUri()
    {
        $service = new Facebook(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            array(),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     * @covers OAuth\OAuth2\Service\Facebook::getAuthorizationEndpoint
     */
    public function testGetAuthorizationEndpoint()
    {
        $service = new Facebook(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertSame('https://www.facebook.com/dialog/oauth', $service->getAuthorizationEndpoint()->getAbsoluteUri());
    }

    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     * @covers OAuth\OAuth2\Service\Facebook::getAccessTokenEndpoint
     */
    public function testGetAccessTokenEndpoint()
    {
        $service = new Facebook(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertSame('https://graph.facebook.com/oauth/access_token', $service->getAccessTokenEndpoint()->getAbsoluteUri());
    }

    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     */
    public function testGetAuthorizationMethod()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnArgument(2));

        $token = $this->getMock('\\OAuth\\OAuth2\\Token\\TokenInterface');
        $token->expects($this->once())->method('getEndOfLife')->will($this->returnValue(TokenInterface::EOL_NEVER_EXPIRES));
        $token->expects($this->once())->method('getAccessToken')->will($this->returnValue('foo'));

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->once())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new Facebook(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage
        );

        $headers = $service->request('https://pieterhordijk.com/my/awesome/path');

        $this->assertTrue(array_key_exists('Authorization', $headers));
        $this->assertTrue(in_array('OAuth foo', $headers, true));
    }

    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     * @covers OAuth\OAuth2\Service\Facebook::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseThrowsExceptionOnError()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('error=some_error'));

        $service = new Facebook(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Http\\Exception\\TokenResponseException');

        $service->requestAccessToken('foo');
    }

    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     * @covers OAuth\OAuth2\Service\Facebook::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseValidWithoutRefreshToken()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('access_token=foo&expires=bar'));

        $service = new Facebook(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Token\\StdOAuth2Token', $service->requestAccessToken('foo'));
    }

    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     * @covers OAuth\OAuth2\Service\Facebook::parseAccessTokenResponse
     */
    public function testParseAccessTokenResponseValidWithRefreshToken()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('access_token=foo&expires=bar&refresh_token=baz'));

        $service = new Facebook(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Token\\StdOAuth2Token', $service->requestAccessToken('foo'));
    }

    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     * @covers OAuth\OAuth2\Service\Facebook::getDialogUri
     */
    public function testGetDialogUriRedirectUriMissing()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');

        $service = new Facebook(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->setExpectedException('\\OAuth\\Common\\Exception\\Exception');

        $service->getDialogUri('feed', array());
    }

    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     * @covers OAuth\OAuth2\Service\Facebook::getDialogUri
     */
    public function testGetDialogUriInstanceofUri()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');

        $service = new Facebook(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $dialogUri = $service->getDialogUri(
            'feed',
            array(
                'redirect_uri' => 'http://www.facebook.com',
                'state' => 'Random state'
            )
        );
        $this->assertInstanceOf('\\OAuth\\Common\\Http\\Uri\\Uri',$dialogUri);
    }

    /**
     * @covers OAuth\OAuth2\Service\Facebook::__construct
     * @covers OAuth\OAuth2\Service\Facebook::getDialogUri
     */
    public function testGetDialogUriContainsAppIdAndOtherParameters()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->any())->method('getConsumerId')->will($this->returnValue('application_id'));


        $service = new Facebook(
            $credentials,
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $dialogUri = $service->getDialogUri(
            'feed',
            array(
                'redirect_uri' => 'http://www.facebook.com',
                'state' => 'Random state'
            )
        );

        $queryString = $dialogUri->getQuery();
        parse_str($queryString, $queryArray);

        $this->assertArrayHasKey('app_id', $queryArray);
        $this->assertArrayHasKey('redirect_uri', $queryArray);
        $this->assertArrayHasKey('state', $queryArray);
    }
}
