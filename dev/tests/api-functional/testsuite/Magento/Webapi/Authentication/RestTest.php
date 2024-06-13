<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Webapi\Authentication;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Authentication\Rest\OauthClient;

/**
 * @magentoApiDataFixture consumerFixture
 */
class RestTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
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

    /** @var \Magento\TestFramework\Authentication\Rest\OauthClient */
    private $_oauthClient;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        $objectManager = Bootstrap::getObjectManager();
        $this->_oauthClient = $objectManager->create(OauthClient::class);
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

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->_oauthClient = null;
        if (isset(self::$_consumer)) {
            self::$_consumer->delete();
            self::$_token->delete();
        }
    }

    public function testGetRequestToken()
    {
        $oauthClient = $this->_oauthClient->create(self::$_consumerKey, self::$_consumerSecret);
        $requestToken = $oauthClient->getRequestToken();

        $this->assertNotEmpty($requestToken["oauth_token"], "Request token value is not set");
        $this->assertNotEmpty($requestToken["oauth_token_secret"], "Request token secret is not set");

        $this->assertEquals(
            \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN,
            strlen($requestToken["oauth_token"]),
            "Request token value length should be " . \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN
        );
        $this->assertEquals(
            \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN_SECRET,
            strlen($requestToken["oauth_token_secret"]),
            "Request token secret length should be " . \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN_SECRET
        );
    }

    /**
     */
    public function testGetRequestTokenExpiredConsumer()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('401 Unauthorized');

        $this::consumerFixture('2012-01-01 00:00:00');
        $this::$_consumer->setUpdatedAt('2012-01-01 00:00:00');
        $this::$_consumer->save();

        $oauthClient = $this->_oauthClient->create(self::$_consumerKey, self::$_consumerSecret);
        $oauthClient->getRequestToken();
    }

    /**
     */
    public function testGetRequestTokenInvalidConsumerKey()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('401 Unauthorized');

        $oauthClient = $this->_oauthClient->create('invalid_key', self::$_consumerSecret);
        $oauthClient->getRequestToken();
    }

    /**
     */
    public function testGetRequestTokenInvalidConsumerSecret()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('401 Unauthorized');

        $oauthClient = $this->_oauthClient->create(self::$_consumerKey, 'invalid_secret');
        $oauthClient->getRequestToken();
    }

    public function testGetAccessToken()
    {
        $oauthClient = $this->_oauthClient->create(self::$_consumerKey, self::$_consumerSecret);
        $requestToken = $oauthClient->getRequestToken();
        $accessToken = $oauthClient->getAccessToken($requestToken, self::$_verifier);

        $this->assertNotEmpty($accessToken["oauth_token"], "Access token value is not set.");
        $this->assertNotEmpty($accessToken["oauth_token_secret"], "Access token secret is not set.");

        $this->assertEquals(
            \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN,
            strlen($accessToken["oauth_token"]),
            "Access token value length should be " . \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN
        );
        $this->assertEquals(
            \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN_SECRET,
            strlen($accessToken["oauth_token_secret"]),
            "Access token secret length should be " . \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN_SECRET
        );
    }

    /**
     */
    public function testGetAccessTokenInvalidVerifier()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('401 Unauthorized');

        $oauthClient = $this->_oauthClient->create(self::$_consumerKey, self::$_consumerSecret);
        $requestToken = $oauthClient->getRequestToken();
        $oauthClient->getAccessToken($requestToken, 'invalid verifier');
    }

    /**
     */
    public function testGetAccessTokenConsumerMismatch()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('401 Unauthorized');

        $oauthClientA = $this->_oauthClient->create(self::$_consumerKey, self::$_consumerSecret);
        $requestTokenA = $oauthClientA->getRequestToken();
        $oauthVerifierA = self::$_verifier;

        self::consumerFixture();
        $oauthClientB = $this->_oauthClient->create(self::$_consumerKey, self::$_consumerSecret);
        $oauthClientB->getRequestToken();
        $oauthClientB->getAccessToken($requestTokenA, $oauthVerifierA);
    }

    /**
     */
    public function testAccessApiInvalidAccessToken()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('400 Bad Request');

        $oauthClient = $this->_oauthClient->create(self::$_consumerKey, self::$_consumerSecret);
        $requestToken = $oauthClient->getRequestToken();
        $accessToken = $oauthClient->getAccessToken(
            $requestToken,
            self::$_verifier
        );

        $accessToken['oauth_token'] = 'invalid';
        $oauthClient->validateAccessToken($accessToken);
    }
}
