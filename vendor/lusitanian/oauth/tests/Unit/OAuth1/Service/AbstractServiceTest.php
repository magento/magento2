<?php

namespace OAuthTest\Unit\OAuth1\Service;

use OAuthTest\Mocks\OAuth1\Service\Mock;

class AbstractServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\OAuth1\Service\AbstractService::__construct
     */
    public function testConstructCorrectInterface()
    {
        $service = $this->getMockForAbstractClass(
            '\\OAuth\\OAuth1\\Service\\AbstractService',
            array(
                $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
                $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
                $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
                $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
                $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface'),
            )
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Service\\ServiceInterface', $service);
    }

    /**
     * @covers OAuth\OAuth1\Service\AbstractService::__construct
     */
    public function testConstructCorrectParent()
    {
        $service = $this->getMockForAbstractClass(
            '\\OAuth\\OAuth1\\Service\\AbstractService',
            array(
                $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
                $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
                $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
                $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
                $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface'),
            )
        );

        $this->assertInstanceOf('\\OAuth\\Common\\Service\\AbstractService', $service);
    }

    /**
     * @covers OAuth\OAuth1\Service\AbstractService::requestRequestToken
     * @covers OAuth\OAuth1\Service\AbstractService::buildAuthorizationHeaderForTokenRequest
     * @covers OAuth\OAuth1\Service\AbstractService::getBasicAuthorizationHeaderInfo
     * @covers OAuth\OAuth1\Service\AbstractService::generateNonce
     * @covers OAuth\OAuth1\Service\AbstractService::getSignatureMethod
     * @covers OAuth\OAuth1\Service\AbstractService::getVersion
     * @covers OAuth\OAuth1\Service\AbstractService::getExtraOAuthHeaders
     * @covers OAuth\OAuth1\Service\AbstractService::parseRequestTokenResponse
     */
    public function testRequestRequestTokenBuildAuthHeaderTokenRequestWithoutParams()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnCallback(function($endpoint, $array, $headers) {
            \PHPUnit_Framework_Assert::assertSame('http://pieterhordijk.com/token', $endpoint->getAbsoluteUri());
        }));

        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Token\\StdOAuth1Token', $service->requestRequestToken());
    }

    /**
     * @covers OAuth\OAuth1\Service\AbstractService::getAuthorizationUri
     * @covers OAuth\OAuth1\Service\AbstractService::getAuthorizationEndpoint
     */
    public function testGetAuthorizationUriWithoutParameters()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertSame('http://pieterhordijk.com/auth', $service->getAuthorizationUri()->getAbsoluteUri());
    }

    /**
     * @covers OAuth\OAuth1\Service\AbstractService::getAuthorizationUri
     * @covers OAuth\OAuth1\Service\AbstractService::getAuthorizationEndpoint
     */
    public function testGetAuthorizationUriWithParameters()
    {
        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertSame('http://pieterhordijk.com/auth?foo=bar&baz=beer', $service->getAuthorizationUri(array(
            'foo' => 'bar',
            'baz' => 'beer',
        ))->getAbsoluteUri());
    }

    /**
     * @covers OAuth\OAuth1\Service\AbstractService::requestAccessToken
     * @covers OAuth\OAuth1\Service\AbstractService::service
     * @covers OAuth\OAuth1\Service\AbstractService::buildAuthorizationHeaderForAPIRequest
     * @covers OAuth\OAuth1\Service\AbstractService::getBasicAuthorizationHeaderInfo
     * @covers OAuth\OAuth1\Service\AbstractService::generateNonce
     * @covers OAuth\OAuth1\Service\AbstractService::getSignatureMethod
     * @covers OAuth\OAuth1\Service\AbstractService::getVersion
     * @covers OAuth\OAuth1\Service\AbstractService::getAccessTokenEndpoint
     * @covers OAuth\OAuth1\Service\AbstractService::getExtraOAuthHeaders
     * @covers OAuth\OAuth1\Service\AbstractService::parseAccessTokenResponse
     */
    public function testRequestAccessTokenWithoutSecret()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnCallback(function($endpoint, $array, $headers) {
            \PHPUnit_Framework_Assert::assertSame('http://pieterhordijk.com/access', $endpoint->getAbsoluteUri());
        }));

        $token = $this->getMock('\\OAuth\\OAuth1\\Token\\TokenInterface');
        $token->expects($this->once())->method('getRequestTokenSecret')->will($this->returnValue('baz'));

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->any())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage,
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Token\\StdOAuth1Token', $service->requestAccessToken('foo', 'bar'));
    }

    /**
     * @covers OAuth\OAuth1\Service\AbstractService::requestAccessToken
     * @covers OAuth\OAuth1\Service\AbstractService::service
     * @covers OAuth\OAuth1\Service\AbstractService::buildAuthorizationHeaderForAPIRequest
     * @covers OAuth\OAuth1\Service\AbstractService::getBasicAuthorizationHeaderInfo
     * @covers OAuth\OAuth1\Service\AbstractService::generateNonce
     * @covers OAuth\OAuth1\Service\AbstractService::getSignatureMethod
     * @covers OAuth\OAuth1\Service\AbstractService::getVersion
     * @covers OAuth\OAuth1\Service\AbstractService::getAccessTokenEndpoint
     * @covers OAuth\OAuth1\Service\AbstractService::getExtraOAuthHeaders
     * @covers OAuth\OAuth1\Service\AbstractService::parseAccessTokenResponse
     */
    public function testRequestAccessTokenWithSecret()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnCallback(function($endpoint, $array, $headers) {
            \PHPUnit_Framework_Assert::assertSame('http://pieterhordijk.com/access', $endpoint->getAbsoluteUri());
        }));

        $token = $this->getMock('\\OAuth\\OAuth1\\Token\\TokenInterface');

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->any())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage,
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Token\\StdOAuth1Token', $service->requestAccessToken('foo', 'bar', $token));
    }

    /**
     * @covers OAuth\OAuth1\Service\AbstractService::request
     * @covers OAuth\OAuth1\Service\AbstractService::determineRequestUriFromPath
     * @covers OAuth\OAuth1\Service\AbstractService::service
     * @covers OAuth\OAuth1\Service\AbstractService::getExtraApiHeaders
     * @covers OAuth\OAuth1\Service\AbstractService::buildAuthorizationHeaderForAPIRequest
     * @covers OAuth\OAuth1\Service\AbstractService::getBasicAuthorizationHeaderInfo
     * @covers OAuth\OAuth1\Service\AbstractService::generateNonce
     * @covers OAuth\OAuth1\Service\AbstractService::getSignatureMethod
     * @covers OAuth\OAuth1\Service\AbstractService::getVersion
     */
    public function testRequest()
    {
        $client = $this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface');
        $client->expects($this->once())->method('retrieveResponse')->will($this->returnValue('response!'));

        $token = $this->getMock('\\OAuth\\OAuth1\\Token\\TokenInterface');
        //$token->expects($this->once())->method('getRequestTokenSecret')->will($this->returnValue('baz'));

        $storage = $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface');
        $storage->expects($this->any())->method('retrieveAccessToken')->will($this->returnValue($token));

        $service = new Mock(
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $client,
            $storage,
            $this->getMock('\\OAuth\\OAuth1\\Signature\\SignatureInterface'),
            $this->getMock('\\OAuth\\Common\\Http\\Uri\\UriInterface')
        );

        $this->assertSame('response!', $service->request('/my/awesome/path'));
    }
}
