<?php

namespace OAuthTest\Unit\OAuth2\Service;

use OAuthTest\Mocks\OAuth2\Service\Mock;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Token\TokenInterface;

class AbstractServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     */
    public function testConstructCorrectInterface()
    {
        $service = $this->getMockForAbstractClass(
            '\\OAuth\\OAuth2\\Service\\AbstractService',
            array(
                $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
                $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
                $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
                array(),
            )
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Service\\ServiceInterface', $service);
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     */
    public function testConstructCorrectParent()
    {
        $service = $this->getMockForAbstractClass(
            '\\OAuth\\OAuth2\\Service\\AbstractService',
            array(
                $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
                $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
                $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
                array(),
            )
        );

        $this->assertInstanceOf('\\OAuth\\Common\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     */
    public function testConstructCorrectParentCustomUri()
    {
        $service = $this->getMockForAbstractClass(
            '\\OAuth\\OAuth2\\Service\\AbstractService',
            array(
                $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
                $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
                $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
                array(),
                $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface'),
            )
        );

        $this->assertInstanceOf('\\OAuth\\Common\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::isValidScope
     */
    public function testConstructThrowsExceptionOnInvalidScope()
    {
        $this->setExpectedException('\\OAuth\\OAuth2\\Service\\Exception\\InvalidScopeException');

        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            array('invalidscope')
        );
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::getAuthorizationUri
     * @covers OAuth\OAuth2\Service\AbstractService::getAuthorizationEndpoint
     */
    public function testGetAuthorizationUriWithoutParametersOrScopes()
    {
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->once())->method('getConsumerId')->will($this->returnValue('foo'));
        $credentials->expects($this->once())->method('getCallbackUrl')->will($this->returnValue('bar'));

        $service = new Mock(
            $credentials,
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertSame(
            'http://pieterhordijk.com/auth?type=web_server&client_id=foo&redirect_uri=bar&response_type=code&scope=',
            $service->getAuthorizationUri()->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::getAuthorizationUri
     * @covers OAuth\OAuth2\Service\AbstractService::getAuthorizationEndpoint
     */
    public function testGetAuthorizationUriWithParametersWithoutScopes()
    {
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->once())->method('getConsumerId')->will($this->returnValue('foo'));
        $credentials->expects($this->once())->method('getCallbackUrl')->will($this->returnValue('bar'));

        $service = new Mock(
            $credentials,
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertSame(
            'http://pieterhordijk.com/auth?foo=bar&baz=beer&type=web_server&client_id=foo&redirect_uri=bar&response_type=code&scope=',
            $service->getAuthorizationUri(array('foo' => 'bar', 'baz' => 'beer'))->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::isValidScope
     * @covers OAuth\OAuth2\Service\AbstractService::getAuthorizationUri
     * @covers OAuth\OAuth2\Service\AbstractService::getAuthorizationEndpoint
     */
    public function testGetAuthorizationUriWithParametersAndScopes()
    {
        $credentials = $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface');
        $credentials->expects($this->once())->method('getConsumerId')->will($this->returnValue('foo'));
        $credentials->expects($this->once())->method('getCallbackUrl')->will($this->returnValue('bar'));

        $service = new Mock(
            $credentials,
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            array('mock', 'mock2')
        );

        $this->assertSame(
            'http://pieterhordijk.com/auth?foo=bar&baz=beer&type=web_server&client_id=foo&redirect_uri=bar&response_type=code&scope=mock+mock2',
            $service->getAuthorizationUri(array('foo' => 'bar', 'baz' => 'beer'))->getAbsoluteUri()
        );
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::requestAccessToken
     * @covers OAuth\OAuth2\Service\AbstractService::getAccessTokenEndpoint
     * @covers OAuth\OAuth2\Service\AbstractService::getExtraOAuthHeaders
     * @covers OAuth\OAuth2\Service\AbstractService::parseAccessTokenResponse
     * @covers OAuth\OAuth2\Service\AbstractService::service
     */
    public function testRequestAccessToken()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceof('\\OAuth\\OAuth2\\Token\\StdOAuth2Token', $service->requestAccessToken('code'));
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::request
     * @covers OAuth\OAuth2\Service\AbstractService::determineRequestUriFromPath
     */
    public function testRequestThrowsExceptionWhenTokenIsExpired()
    {
        $tokenExpiration = new \DateTime('26-03-1984 00:00:00');

        $token = $this->getMock('\\OAuth\\OAuth2\\Token\\TokenInterface');
        $token->expects($this->any())->method('getEndOfLife')->will($this->returnValue($tokenExpiration->format('U')));

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->once())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $storage
        );

        $this->setExpectedException('\\OAuth\\Common\\Token\\Exception\\ExpiredTokenException', 'Token expired on 03/26/1984 at 12:00:00 AM');

        $service->request('https://pieterhordijk.com/my/awesome/path');
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::request
     * @covers OAuth\OAuth2\Service\AbstractService::determineRequestUriFromPath
     * @covers OAuth\OAuth2\Service\AbstractService::getAuthorizationMethod
     * @covers OAuth\OAuth2\Service\AbstractService::parseAccessTokenResponse
     * @covers OAuth\OAuth2\Service\AbstractService::service
     * @covers OAuth\OAuth2\Service\AbstractService::getExtraApiHeaders
     */
    public function testRequestOauthAuthorizationMethod()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnArgument(2));

        $token = $this->getMock('\\OAuth\\OAuth2\\Token\\TokenInterface');
        $token->expects($this->once())->method('getEndOfLife')->will($this->returnValue(TokenInterface::EOL_NEVER_EXPIRES));
        $token->expects($this->once())->method('getAccessToken')->will($this->returnValue('foo'));

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->once())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage
        );

        $headers = $service->request('https://pieterhordijk.com/my/awesome/path');

        $this->assertTrue(array_key_exists('Authorization', $headers));
        $this->assertTrue(in_array('OAuth foo', $headers, true));
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::request
     * @covers OAuth\OAuth2\Service\AbstractService::determineRequestUriFromPath
     * @covers OAuth\OAuth2\Service\AbstractService::getAuthorizationMethod
     * @covers OAuth\OAuth2\Service\AbstractService::parseAccessTokenResponse
     * @covers OAuth\OAuth2\Service\AbstractService::service
     * @covers OAuth\OAuth2\Service\AbstractService::getExtraApiHeaders
     */
    public function testRequestQueryStringMethod()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnArgument(0));

        $token = $this->getMock('\\OAuth\\OAuth2\\Token\\TokenInterface');
        $token->expects($this->once())->method('getEndOfLife')->will($this->returnValue(TokenInterface::EOL_NEVER_EXPIRES));
        $token->expects($this->once())->method('getAccessToken')->will($this->returnValue('foo'));

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->once())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage
        );

        $service->setAuthorizationMethod('querystring');

        $uri         = $service->request('https://pieterhordijk.com/my/awesome/path');
        $absoluteUri = parse_url($uri->getAbsoluteUri());

        $this->assertSame('access_token=foo', $absoluteUri['query']);
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::request
     * @covers OAuth\OAuth2\Service\AbstractService::determineRequestUriFromPath
     * @covers OAuth\OAuth2\Service\AbstractService::getAuthorizationMethod
     * @covers OAuth\OAuth2\Service\AbstractService::parseAccessTokenResponse
     * @covers OAuth\OAuth2\Service\AbstractService::service
     * @covers OAuth\OAuth2\Service\AbstractService::getExtraApiHeaders
     */
    public function testRequestQueryStringTwoMethod()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnArgument(0));

        $token = $this->getMock('\\OAuth\\OAuth2\\Token\\TokenInterface');
        $token->expects($this->once())->method('getEndOfLife')->will($this->returnValue(TokenInterface::EOL_NEVER_EXPIRES));
        $token->expects($this->once())->method('getAccessToken')->will($this->returnValue('foo'));

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->once())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage
        );

        $service->setAuthorizationMethod('querystring2');

        $uri         = $service->request('https://pieterhordijk.com/my/awesome/path');
        $absoluteUri = parse_url($uri->getAbsoluteUri());

        $this->assertSame('oauth2_access_token=foo', $absoluteUri['query']);
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::request
     * @covers OAuth\OAuth2\Service\AbstractService::determineRequestUriFromPath
     * @covers OAuth\OAuth2\Service\AbstractService::getAuthorizationMethod
     * @covers OAuth\OAuth2\Service\AbstractService::parseAccessTokenResponse
     * @covers OAuth\OAuth2\Service\AbstractService::service
     * @covers OAuth\OAuth2\Service\AbstractService::getExtraApiHeaders
     */
    public function testRequestBearerMethod()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnArgument(2));

        $token = $this->getMock('\\OAuth\\OAuth2\\Token\\TokenInterface');
        $token->expects($this->once())->method('getEndOfLife')->will($this->returnValue(TokenInterface::EOL_NEVER_EXPIRES));
        $token->expects($this->once())->method('getAccessToken')->will($this->returnValue('foo'));

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->once())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage
        );

        $service->setAuthorizationMethod('bearer');

        $headers = $service->request('https://pieterhordijk.com/my/awesome/path');

        $this->assertTrue(array_key_exists('Authorization', $headers));
        $this->assertTrue(in_array('Bearer foo', $headers, true));
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::getStorage
     */
    public function testGetStorage()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\\Common\\Storage\\TokenStorageInterface', $service->getStorage());
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::refreshAccessToken
     * @covers OAuth\OAuth2\Service\AbstractService::getAccessTokenEndpoint
     * @covers OAuth\OAuth2\Service\AbstractService::getExtraOAuthHeaders
     * @covers OAuth\OAuth2\Service\AbstractService::parseAccessTokenResponse
     */
    public function testRefreshAccessTokenSuccess()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $token = $this->getMock('\\OAuth\\OAuth2\\Token\\StdOAuth2Token');
        $token->expects($this->once())->method('getRefreshToken')->will($this->returnValue('foo'));

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Token\\StdOAuth2Token', $service->refreshAccessToken($token));
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::isValidScope
     */
    public function testIsValidScopeTrue()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertTrue($service->isValidScope('mock'));
    }

    /**
     * @covers OAuth\OAuth2\Service\AbstractService::__construct
     * @covers OAuth\OAuth2\Service\AbstractService::isValidScope
     */
    public function testIsValidScopeFalse()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertFalse($service->isValidScope('invalid'));
    }
}
