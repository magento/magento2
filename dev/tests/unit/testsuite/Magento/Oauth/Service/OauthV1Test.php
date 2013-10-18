<?php
/**
 * \Magento\Oauth\Service\OauthV1
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Oauth\Service;

class OauthV1Test extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Oauth\Model\Consumer\Factory*/
    private $_consumerFactory;

    /** @var \Magento\Oauth\Model\Nonce\Factory */
    private $_nonceFactory;

    /** @var \Magento\Oauth\Model\Token\Factory */
    private $_tokenFactory;

    /** @var \Magento\Oauth\Model\Consumer */
    private $_consumerMock;

    /** @var \Magento\Oauth\Model\Token */
    private $_tokenMock;

    /** @var \Magento\Core\Model\StoreManagerInterface */
    private $_storeManagerMock;

    /** @var \Magento\HTTP\ZendClient */
    private $_httpClientMock;

    /** @var \Magento\Oauth\Service\OauthV1 */
    private $_service;

    /** @var  \Zend_Oauth_Http_Utility */
    private $_httpUtilityMock;

    /** @var \Magento\Core\Model\Date */
    private $_dateMock;

    /** @var \Magento\Core\Model\Store */
    protected $_storeMock;

    private $_oauthToken;
    private $_oauthSecret;
    private $_oauthVerifier;

    const CONSUMER_ID = 1;

    public function setUp()
    {
        $this->_consumerFactory = $this->getMockBuilder('Magento\Oauth\Model\Consumer\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_consumerMock = $this->getMockBuilder('Magento\Oauth\Model\Consumer')
            ->disableOriginalConstructor()
            // Mocking magic getCreatedAt()
            ->setMethods([
                'getCreatedAt',
                'loadByKey',
                'load',
                'getId',
                'getSecret',
                'getCallbackUrl',
                'save',
                'getData',
                '__wakeup'
            ])->getMock();
        $this->_consumerFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_consumerMock));

        $this->_nonceFactory = $this->getMockBuilder('Magento\Oauth\Model\Nonce\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_tokenFactory = $this->getMockBuilder('Magento\Oauth\Model\Token\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_tokenMock = $this->getMockBuilder('Magento\Oauth\Model\Token')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'load',
                    'getType',
                    'createRequestToken',
                    'getToken',
                    'getSecret',
                    'createVerifierToken',
                    'getVerifier',
                    'getConsumerId',
                    'convertToAccess',
                    'getRevoked',
                    '__wakeup'
                ]
            )->getMock();

        $this->_tokenFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_tokenMock));

        $this->_storeManagerMock = $this->getMockBuilder('Magento\Core\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_storeMock = $this->getMockBuilder('Magento\Core\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->_storeMock));
        $this->_storeMock->expects($this->any())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://www.my-store.com/'));

        $this->_httpClientMock = $this->getMockBuilder('Magento\HTTP\ZendClient')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_httpUtilityMock = $this->getMock('Zend_Oauth_Http_Utility');

        $this->_dateMock = $this->getMockBuilder('Magento\Core\Model\Date')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_service = new \Magento\Oauth\Service\OauthV1(
            $this->_consumerFactory,
            $this->_nonceFactory,
            $this->_tokenFactory,
            $this->_storeManagerMock,
            $this->_httpClientMock,
            $this->_httpUtilityMock,
            $this->_dateMock
        );

        $this->_oauthToken = $this->_generateRandomString(\Magento\Oauth\Model\Token::LENGTH_TOKEN);
        $this->_oauthSecret = $this->_generateRandomString(\Magento\Oauth\Model\Token::LENGTH_SECRET);
        $this->_oauthVerifier = $this->_generateRandomString(\Magento\Oauth\Model\Token::LENGTH_VERIFIER);
    }

    public function tearDown()
    {
        unset($this->_consumerFactory);
        unset($this->_nonceFactory);
        unset($this->_tokenFactory);
        unset($this->_storeManagerMock);
        unset($this->_storeMock);
        unset($this->_httpClientMock);
        unset($this->_dateMock);
        unset($this->_service);
    }

    public function testCreateConsumer()
    {
        $key = $this->_generateRandomString(\Magento\Oauth\Model\Consumer::KEY_LENGTH);
        $secret = $this->_generateRandomString(\Magento\Oauth\Model\Consumer::SECRET_LENGTH);

        $consumerData = array(
            'name' => 'Add-On Name', 'key' => $key, 'secret' => $secret, 'http_post_url' => 'http://www.magento.com');

        $this->_consumerMock->expects($this->once())
            ->method('save')
            ->will($this->returnSelf());
        $this->_consumerMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($consumerData));

        $responseData = $this->_service->createConsumer($consumerData);

        $this->assertEquals($key, $responseData['key'], 'Checking Oauth Consumer Key');
        $this->assertEquals($secret, $responseData['secret'], 'Checking Oauth Consumer Secret');
    }

    public function testPostToConsumer()
    {
        $consumerId = 1;
        $requestData = array('consumer_id' => $consumerId);

        $key = $this->_generateRandomString(\Magento\Oauth\Model\Consumer::KEY_LENGTH);
        $secret = $this->_generateRandomString(\Magento\Oauth\Model\Consumer::SECRET_LENGTH);
        $oauthVerifier = $this->_generateRandomString(\Magento\Oauth\Model\Token::LENGTH_VERIFIER);

        $consumerData = array(
            'entity_id' => $consumerId,
            'key' => $key,
            'secret' => $secret,
            'http_post_url' => 'http://www.magento.com'
        );

        $this->_consumerMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo($consumerId))
            ->will($this->returnSelf());
        $this->_consumerMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($consumerId));
        $this->_consumerMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($consumerData));
        $this->_httpClientMock->expects($this->once())
            ->method('setUri')
            ->with('http://www.magento.com')
            ->will($this->returnSelf());
        $this->_httpClientMock->expects($this->once())
            ->method('setParameterPost')
            ->will($this->returnSelf());
        $this->_tokenMock->expects($this->once())
            ->method('createVerifierToken')
            ->with($consumerId)
            ->will($this->returnSelf());
        $this->_tokenMock->expects($this->any())
            ->method('getVerifier')
            ->will($this->returnValue($oauthVerifier));

        $responseData = $this->_service->postToConsumer($requestData);

        $this->assertEquals($oauthVerifier, $responseData['oauth_verifier']);
    }

    protected function _getRequestTokenParams($amendments = array())
    {
        $requiredParams = [
            'oauth_version' => '1.0',
            'oauth_consumer_key' => $this->_generateRandomString(\Magento\Oauth\Model\Consumer::KEY_LENGTH),
            'oauth_nonce' => '',
            'oauth_timestamp' => time(),
            'oauth_signature_method' => \Magento\Oauth\Service\OauthV1Interface::SIGNATURE_SHA1,
            'http_method' => '',
            'request_url' => 'http://magento.ll',
            'oauth_signature' => 'invalid_signature'
        ];

        return array_merge($requiredParams, $amendments);
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_VERSION_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 1
     */
    public function testGetRequestTokenVersionRejected()
    {
        $this->_service->getRequestToken($this->_getRequestTokenParams(['oauth_version' => '2.0']));
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_CONSUMER_KEY_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 8
     */
    public function testGetRequestTokenConsumerKeyRejected()
    {
        $this->_service->getRequestToken($this->_getRequestTokenParams(['oauth_consumer_key' => 'wrong_key_length']));
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_CONSUMER_KEY_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 8
     */
    public function testGetRequestTokenConsumerKeyNotFound()
    {
        $this->_consumerMock
            ->expects($this->once())
            ->method('loadByKey')
            ->will($this->returnValue(new \Magento\Object()));

        $this->_service->getRequestToken($this->_getRequestTokenParams());
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_CONSUMER_KEY_INVALID
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 17
     */
    public function testGetRequestTokenOutdatedConsumerKey()
    {
        $this->_setupConsumer();
        $this->_dateMock->expects($this->any())->method('timestamp')->will($this->returnValue(9999999999));
        $this->_storeMock->expects($this->once())->method('getConfig')->will($this->returnValue(0));

        $this->_service->getRequestToken($this->_getRequestTokenParams());
    }

    protected function _setupConsumer($isLoadable = true)
    {
        $this->_consumerMock
            ->expects($this->any())
            ->method('loadByKey')
            ->will($this->returnSelf());

        $this->_consumerMock
            ->expects($this->any())
            ->method('getCreatedAt')
            ->will($this->returnValue(date('c', strtotime('-1 day'))));

        if ($isLoadable) {
            $this->_consumerMock->expects($this->any())->method('load')->will($this->returnSelf());
        } else {
            $this->_consumerMock->expects($this->any())->method('load')
                ->will($this->returnValue(new \Magento\Object()));
        }

        $this->_consumerMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_consumerMock->expects($this->any())->method('getSecret')->will($this->returnValue('consumer_secret'));
        $this->_consumerMock->expects($this->any())->method('getCallbackUrl')->will($this->returnValue('callback_url'));
    }

    protected function _makeValidExpirationPeriod()
    {
        $this->_dateMock->expects($this->any())->method('timestamp')->will($this->returnValue(0));
        $this->_storeMock->expects($this->once())->method('getConfig')->will($this->returnValue(300));
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_TIMESTAMP_REFUSED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 4
     * @dataProvider dataProviderForGetRequestTokenNonceTimestampRefusedTest
     */
    public function testGetRequestTokenOauthTimestampRefused($timestamp)
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();

        $this->_service->getRequestToken($this->_getRequestTokenParams(['oauth_timestamp' => $timestamp]));
    }

    public function dataProviderForGetRequestTokenNonceTimestampRefusedTest()
    {
        return [[0], [time() + \Magento\Oauth\Service\OauthV1::TIME_DEVIATION * 2]];
    }

    protected function _setupNonce($isUsed = false, $timestamp = 0)
    {
        $nonceMock = $this->getMockBuilder('Magento\Oauth\Model\Nonce')
            ->disableOriginalConstructor()
            ->setMethods([
                'getConsumerId',
                'loadByCompositeKey',
                'getTimestamp',
                'setNonce',
                'setConsumerId',
                'setTimestamp',
                'save',
                '__wakeup'
            ])->getMock();

        $nonceMock->expects($this->any())->method('getConsumerId')->will($this->returnValue((int)$isUsed));
        $nonceMock->expects($this->any())->method('loadByCompositeKey')->will($this->returnSelf());
        $nonceMock->expects($this->any())->method('getTimestamp')->will($this->returnValue($timestamp));
        $nonceMock->expects($this->any())->method('setNonce')->will($this->returnSelf());
        $nonceMock->expects($this->any())->method('setConsumerId')->will($this->returnSelf());
        $nonceMock->expects($this->any())->method('setTimestamp')->will($this->returnSelf());
        $nonceMock->expects($this->any())->method('save')->will($this->returnSelf());
        $this->_nonceFactory->expects($this->any())->method('create')->will($this->returnValue($nonceMock));
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_NONCE_USED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 5
     */
    public function testGetRequestTokenNonceAlreadyUsed()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce(true);

        $this->_service->getRequestToken($this->_getRequestTokenParams());
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_PARAMETER_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 3
     */
    public function testGetRequestTokenNoConsumer()
    {
        $this->_setupConsumer(false);
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce();

        $this->_service->getRequestToken($this->_getRequestTokenParams());
    }


    /**
     * \Magento\Oauth\Helper\Service::ERR_NONCE_USED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 5
     */
    public function testGetRequestTokenNonceTimestampAlreadyUsed()
    {
        $timestamp = time();
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce(false, $timestamp);

        $this->_service->getRequestToken($this->_getRequestTokenParams(['oauth_timestamp' => $timestamp]));
    }

    protected function _setupToken(
        $doesExist = true,
        $type = \Magento\Oauth\Model\Token::TYPE_VERIFIER,
        $consumerId = self::CONSUMER_ID,
        $verifier = null,
        $isRevoked = false
    ) {
        $this->_tokenMock
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($doesExist ? self::CONSUMER_ID : null));

        $verifier = $verifier ?: $this->_oauthVerifier;

        $this->_tokenMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->_tokenMock->expects($this->any())->method('getType')->will($this->returnValue($type));
        $this->_tokenMock->expects($this->any())->method('createRequestToken')->will($this->returnSelf());
        $this->_tokenMock->expects($this->any())->method('getToken')->will($this->returnValue($this->_oauthToken));
        $this->_tokenMock->expects($this->any())->method('getSecret')->will($this->returnValue($this->_oauthSecret));
        $this->_tokenMock->expects($this->any())->method('getConsumerId')->will($this->returnValue($consumerId));
        $this->_tokenMock->expects($this->any())->method('getVerifier')->will($this->returnValue($verifier));
        $this->_tokenMock->expects($this->any())->method('convertToAccess')->will($this->returnSelf());
        $this->_tokenMock->expects($this->any())->method('getRevoked')->will($this->returnValue($isRevoked));
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_TOKEN_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 12
     */
    public function testGetRequestTokenTokenRejected()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce();
        $this->_setupToken(false);

        $this->_service->getRequestToken($this->_getRequestTokenParams());
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_TOKEN_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 12
     */
    public function testGetRequestTokenTokenRejectedByType()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Oauth\Model\Token::TYPE_REQUEST); // wrong type

        $this->_service->getRequestToken($this->_getRequestTokenParams());
    }


    /**
     * \Magento\Oauth\Helper\Service::ERR_SIGNATURE_METHOD_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 6
     */
    public function testGetRequestTokenSignatureMethodRejected()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce();
        $this->_setupToken();

        $this->_service->getRequestToken($this->_getRequestTokenParams(['oauth_signature_method' => 'wrong_method']));
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_SIGNATURE_INVALID
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 7
     */
    public function testGetRequestTokenInvalidSignature()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce();
        $this->_setupToken();

        $this->_service->getRequestToken($this->_getRequestTokenParams(['oauth_signature' => 'invalid_signature']));
    }

    public function testGetRequestToken()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce();
        $this->_setupToken();

        $signature = 'valid_signature';
        $this->_httpUtilityMock->expects($this->any())->method('sign')->will($this->returnValue($signature));

        $requestToken = $this->_service->getRequestToken(
            $this->_getRequestTokenParams(['oauth_signature' => $signature])
        );

        $this->assertEquals(
            ['oauth_token' => $this->_oauthToken, 'oauth_token_secret' => $this->_oauthSecret],
            $requestToken
        );
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_VERSION_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 1
     */
    public function testGetAccessTokenVersionRejected()
    {
        $this->_service->getAccessToken($this->_getAccessTokenRequiredParams(['oauth_version' => '0.0']));
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_PARAMETER_ABSENT
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 2
     */
    public function testGetAccessTokenParameterAbsent()
    {
        $this->_service->getAccessToken([
            'oauth_version' => '1.0',
            'oauth_consumer_key' => '',
            'oauth_signature' => '',
            'oauth_signature_method' => '',
            'oauth_nonce' => '',
            'oauth_timestamp' => '',
            'oauth_token' => '',
            // oauth_verifier missing
        ]);
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_TOKEN_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 12
     */
    public function testGetAccessTokenTokenRejected()
    {
        $this->_service->getAccessToken($this->_getAccessTokenRequiredParams(['oauth_token' => 'invalid_token']));
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_SIGNATURE_METHOD_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 6
     */
    public function testGetAccessTokenSignatureMethodRejected()
    {
        $this->_service->getAccessToken(
            $this->_getAccessTokenRequiredParams(['oauth_signature_method' => 'invalid_method'])
        );
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_TOKEN_USED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 9
     */
    public function testGetAccessTokenTokenUsed()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Oauth\Model\Token::TYPE_VERIFIER); // Wrong type

        $this->_service->getAccessToken($this->_getAccessTokenRequiredParams());
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_TOKEN_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 12
     */
    public function testGetAccessTokenConsumerIdDoesntMatch()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Oauth\Model\Token::TYPE_REQUEST, null); // $token->getConsumerId() === null

        $this->_service->getAccessToken($this->_getAccessTokenRequiredParams());
    }

    /**
     * \Magento\Oauth\Helper\Data::ERR_VERIFIER_INVALID
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 13
     * @dataProvider dataProviderForGetAccessTokenVerifierInvalidTest
     */
    public function testGetAccessTokenVerifierInvalid($verifier, $verifierFromToken)
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Oauth\Model\Token::TYPE_REQUEST, self::CONSUMER_ID, $verifierFromToken);

        $this->_service->getAccessToken($this->_getAccessTokenRequiredParams(['oauth_verifier' => $verifier]));
    }

    public function dataProviderForGetAccessTokenVerifierInvalidTest()
    {
        return [
            [3, 3], // Verifier is not a string
            ['wrong_length', 'wrong_length'],
            ['verifier', 'doesnt match']
        ];
    }

    public function testGetAccessToken()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Oauth\Model\Token::TYPE_REQUEST);

        $token = $this->_service->getAccessToken($this->_getAccessTokenRequiredParams());
        $this->assertEquals(['oauth_token' => $this->_oauthToken, 'oauth_token_secret' => $this->_oauthSecret], $token);
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_TOKEN_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 12
     */
    public function testValidateAccessTokenRequestTokenRejected()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Oauth\Model\Token::TYPE_ACCESS, null); // $token->getConsumerId() === null

        $this->_service->validateAccessTokenRequest($this->_getAccessTokenRequiredParams());
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_TOKEN_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 12
     */
    public function testValidateAccessTokenRequestTokenRejectedByType()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Oauth\Model\Token::TYPE_REQUEST);

        $this->_service->validateAccessTokenRequest($this->_getAccessTokenRequiredParams());
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_TOKEN_REVOKED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 11
     */
    public function testValidateAccessTokenRequestTokenRevoked()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true,
                           \Magento\Oauth\Model\Token::TYPE_ACCESS,
                           self::CONSUMER_ID,
                           $this->_oauthVerifier,
                           true);

        $this->_service->validateAccessTokenRequest($this->_getAccessTokenRequiredParams());
    }

    public function testValidateAccessTokenRequest()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Oauth\Model\Token::TYPE_ACCESS);

        $this->assertTrue($this->_service->validateAccessTokenRequest
                              ($this->_getAccessTokenRequiredParams())['isValid']);
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_TOKEN_REJECTED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 12
     */
    public function testValidateAccessTokenRejectedByType()
    {
        $this->_setupConsumer();
        $this->_setupToken(true, \Magento\Oauth\Model\Token::TYPE_REQUEST);

        $this->_service->validateAccessToken(['token' => $this->_oauthToken]);
    }

    /**
     * \Magento\Oauth\Helper\Service::ERR_TOKEN_REVOKED
     * @expectedException \Magento\Oauth\Exception
     * @expectedExceptionCode 11
     */
    public function testValidateAccessTokenRevoked()
    {
        $this->_setupConsumer();
        $this->_setupToken(true,
                           \Magento\Oauth\Model\Token::TYPE_ACCESS,
                           self::CONSUMER_ID,
                           $this->_oauthVerifier,
                           true);

        $this->_service->validateAccessToken(['token' => $this->_oauthToken]);
    }

    public function testValidateAccessToken()
    {
        $this->_setupConsumer();
        $this->_setupToken(true, \Magento\Oauth\Model\Token::TYPE_ACCESS);

        $this->assertTrue($this->_service->validateAccessToken(array('token' => $this->_oauthToken))['isValid']);
    }

    protected function _getAccessTokenRequiredParams($amendments = array())
    {
        $requiredParams = [
            'oauth_consumer_key' => $this->_generateRandomString(\Magento\Oauth\Model\Consumer::KEY_LENGTH),
            'oauth_signature' => '',
            'oauth_signature_method' => (string)\Magento\Oauth\Service\OauthV1Interface::SIGNATURE_SHA1,
            'oauth_nonce' => '',
            'oauth_timestamp' => (string)time(),
            'oauth_token' => $this->_generateRandomString(\Magento\Oauth\Model\Token::LENGTH_TOKEN),
            'oauth_verifier' => $this->_oauthVerifier,
            'request_url' => '',
            'http_method' => '',
        ];

        return array_merge($requiredParams, $amendments);
    }

    private function _generateRandomString($length)
    {
        return substr(str_shuffle(
                str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)), 0, $length);
    }
}
