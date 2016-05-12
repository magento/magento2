<?php
/**
 * Test authentication mechanisms in REST.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Authentication;

/**
 * @magentoApiDataFixture consumerFixture
 */
class RestTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /** @var \Magento\TestFramework\Authentication\Rest\OauthClient[] */
    protected $_oAuthClients = [];

    /** @var \Magento\Integration\Model\Oauth\Consumer */
    protected static $_consumer;

    /** @var \Magento\Integration\Model\Oauth\Token */
    protected static $_token;

    /** @var string */
    protected static $_consumerKey;

    /** @var string */
    protected static $_consumerSecret;

    /** @var string */
    protected static $_verifier;

    protected function setUp()
    {
        $this->_markTestAsRestOnly();
        parent::setUp();
    }

    /**
     * Create a consumer
     */
    public static function consumerFixture($date = null)
    {
        /** Clear the credentials because during the fixture generation, any previous credentials are invalidated */
        \Magento\TestFramework\Authentication\OauthHelper::clearApiAccessCredentials();

        $consumerCredentials = \Magento\TestFramework\Authentication\OauthHelper::getConsumerCredentials($date);
        self::$_consumerKey = $consumerCredentials['key'];
        self::$_consumerSecret = $consumerCredentials['secret'];
        self::$_verifier = $consumerCredentials['verifier'];
        self::$_consumer = $consumerCredentials['consumer'];
        self::$_token = $consumerCredentials['token'];
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->_oAuthClients = [];
        if (isset(self::$_consumer)) {
            self::$_consumer->delete();
            self::$_token->delete();
        }
    }

    public function testGetRequestToken()
    {
        /** @var $oAuthClient \Magento\TestFramework\Authentication\Rest\OauthClient */
        $oAuthClient = $this->_getOauthClient(self::$_consumerKey, self::$_consumerSecret);
        $requestToken = $oAuthClient->requestRequestToken();

        $this->assertNotEmpty($requestToken->getRequestToken(), "Request token value is not set");
        $this->assertNotEmpty($requestToken->getRequestTokenSecret(), "Request token secret is not set");

        $this->assertEquals(
            \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN,
            strlen($requestToken->getRequestToken()),
            "Request token value length should be " . \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN
        );
        $this->assertEquals(
            \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN_SECRET,
            strlen($requestToken->getRequestTokenSecret()),
            "Request token secret length should be " . \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN_SECRET
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage HTTP/1.1 401
     */
    public function testGetRequestTokenExpiredConsumer()
    {
        $this::consumerFixture('2012-01-01 00:00:00');
        $this::$_consumer->setUpdatedAt('2012-01-01 00:00:00');
        $this::$_consumer->save();
        /** @var $oAuthClient \Magento\TestFramework\Authentication\Rest\OauthClient */
        $oAuthClient = $this->_getOauthClient(self::$_consumerKey, self::$_consumerSecret);
        $oAuthClient->requestRequestToken();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage HTTP/1.1 401
     */
    public function testGetRequestTokenInvalidConsumerKey()
    {
        $oAuthClient = $this->_getOauthClient('invalid_key', self::$_consumerSecret);
        $oAuthClient->requestRequestToken();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage HTTP/1.1 401
     */
    public function testGetRequestTokenInvalidConsumerSecret()
    {
        $oAuthClient = $this->_getOauthClient(self::$_consumerKey, 'invalid_secret');
        $oAuthClient->requestRequestToken();
    }

    public function testGetAccessToken()
    {
        $oAuthClient = $this->_getOauthClient(self::$_consumerKey, self::$_consumerSecret);
        $requestToken = $oAuthClient->requestRequestToken();
        $accessToken = $oAuthClient->requestAccessToken(
            $requestToken->getRequestToken(),
            self::$_verifier,
            $requestToken->getRequestTokenSecret()
        );
        $this->assertNotEmpty($accessToken->getAccessToken(), "Access token value is not set.");
        $this->assertNotEmpty($accessToken->getAccessTokenSecret(), "Access token secret is not set.");

        $this->assertEquals(
            \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN,
            strlen($accessToken->getAccessToken()),
            "Access token value length should be " . \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN
        );
        $this->assertEquals(
            \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN_SECRET,
            strlen($accessToken->getAccessTokenSecret()),
            "Access token secret length should be " . \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN_SECRET
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage HTTP/1.1 401
     */
    public function testGetAccessTokenInvalidVerifier()
    {
        $oAuthClient = $this->_getOauthClient(self::$_consumerKey, self::$_consumerSecret);
        $requestToken = $oAuthClient->requestRequestToken();
        $oAuthClient->requestAccessToken(
            $requestToken->getRequestToken(),
            'invalid verifier',
            $requestToken->getRequestTokenSecret()
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage HTTP/1.1 401
     */
    public function testGetAccessTokenConsumerMismatch()
    {
        $oAuthClientA = $this->_getOauthClient(self::$_consumerKey, self::$_consumerSecret);
        $requestTokenA = $oAuthClientA->requestRequestToken();
        $oauthVerifierA = self::$_verifier;

        self::consumerFixture();
        $oAuthClientB = $this->_getOauthClient(self::$_consumerKey, self::$_consumerSecret);

        $oAuthClientB->requestAccessToken(
            $requestTokenA->getRequestToken(),
            $oauthVerifierA,
            $requestTokenA->getRequestTokenSecret()
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage HTTP/1.1 400
     */
    public function testAccessApiInvalidAccessToken()
    {
        $oAuthClient = $this->_getOauthClient(self::$_consumerKey, self::$_consumerSecret);
        $requestToken = $oAuthClient->requestRequestToken();
        $accessToken = $oAuthClient->requestAccessToken(
            $requestToken->getRequestToken(),
            self::$_verifier,
            $requestToken->getRequestTokenSecret()
        );
        $accessToken->setAccessToken('invalid');
        $oAuthClient->validateAccessToken($accessToken);
    }

    protected function _getOauthClient($consumerKey, $consumerSecret)
    {
        if (!isset($this->_oAuthClients[$consumerKey])) {
            $credentials = new \OAuth\Common\Consumer\Credentials($consumerKey, $consumerSecret, TESTS_BASE_URL);
            $this->_oAuthClients[$consumerKey] = new \Magento\TestFramework\Authentication\Rest\OauthClient(
                $credentials
            );
        }
        return $this->_oAuthClients[$consumerKey];
    }
}
