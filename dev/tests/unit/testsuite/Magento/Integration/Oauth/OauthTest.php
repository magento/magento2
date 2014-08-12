<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Oauth;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OauthTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Integration\Model\Oauth\Consumer\Factory */
    private $_consumerFactory;

    /** @var \Magento\Integration\Model\Oauth\Nonce\Factory */
    private $_nonceFactory;

    /** @var \Magento\Integration\Model\Oauth\Token\Factory */
    private $_tokenFactory;

    /** @var \Magento\Integration\Model\Oauth\Consumer */
    private $_consumerMock;

    /** @var \Magento\Integration\Model\Oauth\Token */
    private $_tokenMock;

    /** @var \Magento\Framework\Oauth\Helper\Oauth */
    private $_oauthHelperMock;

    /** @var \Magento\Integration\Helper\Oauth\Data */
    private $_dataHelperMock;

    /** @var \Magento\Framework\Oauth\Oauth */
    private $_oauth;

    /** @var  \Zend_Oauth_Http_Utility */
    private $_httpUtilityMock;

    /** @var \Magento\Framework\Stdlib\DateTime\DateTime */
    private $_dateMock;

    private $_oauthToken;

    private $_oauthSecret;

    private $_oauthVerifier;

    const CONSUMER_ID = 1;

    const REQUEST_URL = 'http://magento.ll';

    public function setUp()
    {
        $this->_consumerFactory = $this->getMockBuilder('Magento\Integration\Model\Oauth\Consumer\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_consumerMock = $this->getMockBuilder('Magento\Integration\Model\Oauth\Consumer')
            ->disableOriginalConstructor()->setMethods(
                array(
                    'getCreatedAt',
                    'loadByKey',
                    'load',
                    'getId',
                    'getSecret',
                    'getCallbackUrl',
                    'save',
                    'getData',
                    '__wakeup'
                )
            )
            ->getMock();
        $this->_consumerFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_consumerMock));
        $this->_nonceFactory = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\Nonce\Factory'
        )->disableOriginalConstructor()->getMock();
        $this->_tokenFactory = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\Token\Factory'
        )->disableOriginalConstructor()->getMock();
        $this->_tokenMock = $this->getMockBuilder('Magento\Integration\Model\Oauth\Token')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
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
                    'getResource',
                    'loadByConsumerIdAndUserType',
                    '__wakeup'
                )
            )
            ->getMock();
        $this->_tokenFactory->expects($this->any())->method('create')->will($this->returnValue($this->_tokenMock));
        $this->_oauthHelperMock = $this->getMockBuilder('Magento\Framework\Oauth\Helper\Oauth')
            ->setConstructorArgs(array(new \Magento\Framework\Math\Random()))
            ->getMock();
        $this->_dataHelperMock = $this->getMockBuilder(
            'Magento\Integration\Helper\Oauth\Data'
        )->disableOriginalConstructor()->getMock();
        $this->_httpUtilityMock = $this->getMockBuilder('Zend_Oauth_Http_Utility')
            ->setMethods(array('sign'))
            ->getMock();
        $this->_dateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();

        $nonceGenerator = new \Magento\Integration\Model\Oauth\Nonce\Generator(
            $this->_oauthHelperMock,
            $this->_nonceFactory,
            $this->_dateMock
        );
        $tokenProvider = new \Magento\Integration\Model\Oauth\Token\Provider(
            $this->_consumerFactory,
            $this->_tokenFactory,
            $this->_dataHelperMock,
            $this->_dateMock,
            $this->_tokenMock
        );
        $this->_oauth = new \Magento\Framework\Oauth\Oauth(
            $this->_oauthHelperMock,
            $nonceGenerator,
            $tokenProvider,
            $this->_httpUtilityMock
        );
        $this->_oauthToken = $this->_generateRandomString(\Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN);
        $this->_oauthSecret = $this->_generateRandomString(\Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN_SECRET);
        $this->_oauthVerifier = $this->_generateRandomString(
            \Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN_VERIFIER
        );
    }

    public function tearDown()
    {
        unset($this->_consumerFactory);
        unset($this->_nonceFactory);
        unset($this->_tokenFactory);
        unset($this->_oauthHelperMock);
        unset($this->_dataHelperMock);
        unset($this->_httpUtilityMock);
        unset($this->_dateMock);
        unset($this->_oauth);
    }

    protected function _getRequestTokenParams($amendments = array())
    {
        $requiredParams = array(
            'oauth_version' => '1.0',
            'oauth_consumer_key' => $this->_generateRandomString(
                    \Magento\Framework\Oauth\Helper\Oauth::LENGTH_CONSUMER_KEY
                ),
            'oauth_nonce' => '',
            'oauth_timestamp' => time(),
            'oauth_signature_method' => \Magento\Framework\Oauth\OauthInterface::SIGNATURE_SHA1,
            'oauth_signature' => 'invalid_signature'
        );

        return array_merge($requiredParams, $amendments);
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_VERSION_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\OauthInputException
     */
    public function testGetRequestTokenVersionRejected()
    {
        $this->_oauth->getRequestToken(
            $this->_getRequestTokenParams(array('oauth_version' => '2.0')),
            self::REQUEST_URL
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_CONSUMER_KEY_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testGetRequestTokenConsumerKeyRejected()
    {
        $this->_oauth->getRequestToken(
            $this->_getRequestTokenParams(array('oauth_consumer_key' => 'wrong_key_length')),
            self::REQUEST_URL
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_CONSUMER_KEY_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testGetRequestTokenConsumerKeyNotFound()
    {
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'loadByKey'
        )->will(
            $this->returnValue(new \Magento\Framework\Object())
        );

        $this->_oauth->getRequestToken($this->_getRequestTokenParams(), self::REQUEST_URL);
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_CONSUMER_KEY_INVALID
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testGetRequestTokenOutdatedConsumerKey()
    {
        $this->_setupConsumer();
        $this->_dateMock->expects($this->any())->method('timestamp')->will($this->returnValue(9999999999));
        $this->_dataHelperMock->expects(
            $this->once()
        )->method(
            'getConsumerExpirationPeriod'
        )->will(
            $this->returnValue(0)
        );

        $this->_oauth->getRequestToken($this->_getRequestTokenParams(), self::REQUEST_URL);
    }

    protected function _setupConsumer($isLoadable = true)
    {
        $this->_consumerMock->expects($this->any())->method('loadByKey')->will($this->returnSelf());

        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'getCreatedAt'
        )->will(
            $this->returnValue(date('c', strtotime('-1 day')))
        );

        if ($isLoadable) {
            $this->_consumerMock->expects($this->any())->method('load')->will($this->returnSelf());
        } else {
            $this->_consumerMock->expects(
                $this->any()
            )->method(
                'load'
            )->will(
                $this->returnValue(new \Magento\Framework\Object())
            );
        }

        $this->_consumerMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->_consumerMock->expects($this->any())->method('getSecret')->will($this->returnValue('consumer_secret'));
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'getCallbackUrl'
        )->will(
            $this->returnValue('callback_url')
        );
    }

    protected function _makeValidExpirationPeriod()
    {
        $this->_dateMock->expects($this->any())->method('timestamp')->will($this->returnValue(0));
        $this->_dataHelperMock->expects(
            $this->once()
        )->method(
            'getConsumerExpirationPeriod'
        )->will(
            $this->returnValue(300)
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TIMESTAMP_REFUSED
     *
     * @expectedException \Magento\Framework\Oauth\OauthInputException
     * @dataProvider dataProviderForGetRequestTokenNonceTimestampRefusedTest
     */
    public function testGetRequestTokenOauthTimestampRefused($timestamp)
    {
        $this->markTestIncomplete('MAGETWO-19388');
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();

        $this->_oauth->getRequestToken(
            $this->_getRequestTokenParams(array('oauth_timestamp' => $timestamp)),
            self::REQUEST_URL
        );
    }

    public function dataProviderForGetRequestTokenNonceTimestampRefusedTest()
    {
        return array(array(0), array(time() + \Magento\Integration\Model\Oauth\Nonce\Generator::TIME_DEVIATION * 2));
    }

    protected function _setupNonce($isUsed = false, $timestamp = 0)
    {
        $nonceMock = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\Nonce'
        )->disableOriginalConstructor()->setMethods(
            array(
                'loadByCompositeKey',
                'getNonce',
                'getTimestamp',
                'setNonce',
                'setConsumerId',
                'setTimestamp',
                'save',
                '__wakeup'
            )
        )->getMock();

        $nonceMock->expects($this->any())->method('getNonce')->will($this->returnValue($isUsed));
        $nonceMock->expects($this->any())->method('loadByCompositeKey')->will($this->returnSelf());
        $nonceMock->expects($this->any())->method('getTimestamp')->will($this->returnValue($timestamp));
        $nonceMock->expects($this->any())->method('setNonce')->will($this->returnSelf());
        $nonceMock->expects($this->any())->method('setConsumerId')->will($this->returnSelf());
        $nonceMock->expects($this->any())->method('setTimestamp')->will($this->returnSelf());
        $nonceMock->expects($this->any())->method('save')->will($this->returnSelf());
        $this->_nonceFactory->expects($this->any())->method('create')->will($this->returnValue($nonceMock));
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_NONCE_USED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testGetRequestTokenNonceAlreadyUsed()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce(true);

        $this->_oauth->getRequestToken($this->_getRequestTokenParams(), self::REQUEST_URL);
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_CONSUMER_KEY_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testGetRequestTokenNoConsumer()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'loadByKey'
        )->will(
            $this->returnValue(new \Magento\Framework\Object())
        );

        $this->_oauth->getRequestToken($this->_getRequestTokenParams(), self::REQUEST_URL);
    }

    protected function _setupToken(
        $doesExist = true,
        $type = \Magento\Integration\Model\Oauth\Token::TYPE_VERIFIER,
        $consumerId = self::CONSUMER_ID,
        $verifier = null,
        $isRevoked = false
    ) {
        $this->_tokenMock->expects(
            $this->any()
        )->method(
            'getId'
        )->will(
            $this->returnValue($doesExist ? self::CONSUMER_ID : null)
        );

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
        $this->_tokenMock->expects($this->any())->method('loadByConsumerIdAndUserType')->will($this->returnSelf());
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TOKEN_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testGetRequestTokenTokenRejected()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce();
        $this->_setupToken(false);

        $signature = 'valid_signature';
        $this->_httpUtilityMock->expects($this->any())->method('sign')->will($this->returnValue($signature));

        $this->_oauth->getRequestToken(
            $this->_getRequestTokenParams(array('oauth_signature' => $signature)),
            self::REQUEST_URL
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TOKEN_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testGetRequestTokenTokenRejectedByType()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Integration\Model\Oauth\Token::TYPE_REQUEST);
        // wrong type

        $signature = 'valid_signature';
        $this->_httpUtilityMock->expects($this->any())->method('sign')->will($this->returnValue($signature));

        $this->_oauth->getRequestToken(
            $this->_getRequestTokenParams(array('oauth_signature' => $signature)),
            self::REQUEST_URL
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_SIGNATURE_METHOD_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\OauthInputException
     */
    public function testGetRequestTokenSignatureMethodRejected()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce();
        $this->_setupToken();

        $this->_oauth->getRequestToken(
            $this->_getRequestTokenParams(array('oauth_signature_method' => 'wrong_method')),
            self::REQUEST_URL
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_SIGNATURE_INVALID
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testGetRequestTokenInvalidSignature()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce();
        $this->_setupToken();

        $this->_oauth->getRequestToken(
            $this->_getRequestTokenParams(array('oauth_signature' => 'invalid_signature')),
            self::REQUEST_URL
        );
    }

    public function testGetRequestToken()
    {
        $this->_setupConsumer();
        $this->_makeValidExpirationPeriod();
        $this->_setupNonce();
        $this->_setupToken();

        $signature = 'valid_signature';
        $this->_httpUtilityMock->expects($this->any())->method('sign')->will($this->returnValue($signature));

        $requestToken = $this->_oauth->getRequestToken(
            $this->_getRequestTokenParams(array('oauth_signature' => $signature)),
            self::REQUEST_URL
        );

        $this->assertEquals(
            array('oauth_token' => $this->_oauthToken, 'oauth_token_secret' => $this->_oauthSecret),
            $requestToken
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_VERSION_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\OauthInputException
     */
    public function testGetAccessTokenVersionRejected()
    {
        $this->_oauth->getAccessToken(
            $this->_getAccessTokenRequiredParams(array('oauth_version' => '0.0')),
            self::REQUEST_URL
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_PARAMETER_ABSENT
     *
     * @expectedException \Magento\Framework\Oauth\OauthInputException
     * @expectedExceptionMessage oauth_verifier is a required field.
     */
    public function testGetAccessTokenParameterAbsent()
    {
        $this->_oauth->getAccessToken(
            array(
                'oauth_version' => '1.0',
                'oauth_consumer_key' => '',
                'oauth_signature' => '',
                'oauth_signature_method' => '',
                'oauth_nonce' => '',
                'oauth_timestamp' => '',
                'oauth_token' => ''
                // oauth_verifier missing
            ),
            self::REQUEST_URL
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TOKEN_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testGetAccessTokenTokenRejected()
    {
        $this->_oauth->getAccessToken(
            $this->_getAccessTokenRequiredParams(array('oauth_token' => 'invalid_token')),
            self::REQUEST_URL
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_SIGNATURE_METHOD_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\OauthInputException
     */
    public function testGetAccessTokenSignatureMethodRejected()
    {
        $this->_oauth->getAccessToken(
            $this->_getAccessTokenRequiredParams(array('oauth_signature_method' => 'invalid_method')),
            self::REQUEST_URL
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TOKEN_USED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testGetAccessTokenTokenUsed()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Integration\Model\Oauth\Token::TYPE_VERIFIER);
        // Wrong type

        $this->_oauth->getAccessToken($this->_getAccessTokenRequiredParams(), self::REQUEST_URL);
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TOKEN_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testGetAccessTokenConsumerIdDoesntMatch()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Integration\Model\Oauth\Token::TYPE_REQUEST, null);

        $this->_oauth->getAccessToken($this->_getAccessTokenRequiredParams(), self::REQUEST_URL);
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_VERIFIER_INVALID
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     * @dataProvider dataProviderForGetAccessTokenVerifierInvalidTest
     */
    public function testGetAccessTokenVerifierInvalid($verifier, $verifierFromToken)
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(
            true,
            \Magento\Integration\Model\Oauth\Token::TYPE_REQUEST,
            self::CONSUMER_ID,
            $verifierFromToken
        );

        $this->_oauth->getAccessToken(
            $this->_getAccessTokenRequiredParams(array('oauth_verifier' => $verifier)),
            self::REQUEST_URL
        );
    }

    public function dataProviderForGetAccessTokenVerifierInvalidTest()
    {
        // Verifier is not a string
        return array(array(3, 3), array('wrong_length', 'wrong_length'), array('verifier', 'doesnt match'));
    }

    public function testGetAccessToken()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Integration\Model\Oauth\Token::TYPE_REQUEST);

        $token = $this->_oauth->getAccessToken($this->_getAccessTokenRequiredParams(), self::REQUEST_URL);
        $this->assertEquals(
            array('oauth_token' => $this->_oauthToken, 'oauth_token_secret' => $this->_oauthSecret),
            $token
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TOKEN_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testValidateAccessTokenRequestTokenRejected()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Integration\Model\Oauth\Token::TYPE_ACCESS, null);

        $this->_oauth->validateAccessTokenRequest($this->_getAccessTokenRequiredParams(), self::REQUEST_URL);
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TOKEN_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testValidateAccessTokenRequestTokenRejectedByType()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Integration\Model\Oauth\Token::TYPE_REQUEST);

        $this->_oauth->validateAccessTokenRequest($this->_getAccessTokenRequiredParams(), self::REQUEST_URL);
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TOKEN_REVOKED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testValidateAccessTokenRequestTokenRevoked()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(
            true,
            \Magento\Integration\Model\Oauth\Token::TYPE_ACCESS,
            self::CONSUMER_ID,
            $this->_oauthVerifier,
            true
        );

        $this->_oauth->validateAccessTokenRequest($this->_getAccessTokenRequiredParams(), self::REQUEST_URL);
    }

    public function testValidateAccessTokenRequest()
    {
        $this->_setupConsumer();
        $this->_setupNonce();
        $this->_setupToken(true, \Magento\Integration\Model\Oauth\Token::TYPE_ACCESS);
        $requiredParams = $this->_getAccessTokenRequiredParams();
        $this->assertEquals(
            1,
            $this->_oauth->validateAccessTokenRequest($requiredParams, self::REQUEST_URL),
            "Consumer ID is invalid."
        );
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TOKEN_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testValidateAccessTokenRejectedByType()
    {
        $this->_setupConsumer();
        $this->_setupToken(true, \Magento\Integration\Model\Oauth\Token::TYPE_REQUEST);

        $this->_oauth->validateAccessToken($this->_oauthToken);
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TOKEN_REVOKED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testValidateAccessTokenRevoked()
    {
        $this->_setupConsumer();
        $this->_setupToken(
            true,
            \Magento\Integration\Model\Oauth\Token::TYPE_ACCESS,
            self::CONSUMER_ID,
            $this->_oauthVerifier,
            true
        );

        $this->_oauth->validateAccessToken($this->_oauthToken);
    }

    /**
     * \Magento\Framework\Oauth\OauthInterface::ERR_TOKEN_REJECTED
     *
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testValidateAccessTokenNoConsumer()
    {
        $this->_setupConsumer(false);
        $this->_setupToken(true, \Magento\Integration\Model\Oauth\Token::TYPE_ACCESS);

        $this->_oauth->validateAccessToken($this->_oauthToken);
    }

    public function testValidateAccessToken()
    {
        $this->_setupConsumer();
        $this->_setupToken(true, \Magento\Integration\Model\Oauth\Token::TYPE_ACCESS);

        $this->assertEquals(1, $this->_oauth->validateAccessToken($this->_oauthToken), "Consumer ID is invalid.");
    }

    public function testBuildAuthorizationHeader()
    {
        $signature = 'valid_signature';
        $this->_httpUtilityMock->expects($this->any())->method('sign')->will($this->returnValue($signature));

        $this->_setupConsumer(false);
        $this->_oauthHelperMock->expects(
            $this->any()
        )->method(
            'generateRandomString'
        )->will(
            $this->returnValue('tyukmnjhgfdcvxstyuioplkmnhtfvert')
        );

        $request = array(
            'oauth_consumer_key' => 'edf957ef88492f0a32eb7e1731e85da2',
            'oauth_consumer_secret' => 'asdawwewefrtyh2f0a32eb7e1731e85d',
            'oauth_token' => '7c0709f789e1f38a17aa4b9a28e1b06c',
            'oauth_token_secret' => 'a6agsfrsfgsrjjjjyy487939244ssggg',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        );

        $requestUrl = 'http://www.example.com/endpoint';
        $oauthHeader = $this->_oauth->buildAuthorizationHeader($request, $requestUrl);

        $expectedHeader = 'OAuth oauth_nonce="tyukmnjhgfdcvxstyuioplkmnhtfvert",' .
            'oauth_timestamp="",' .
            'oauth_version="1.0",oauth_consumer_key="edf957ef88492f0a32eb7e1731e85da2",' .
            'oauth_consumer_secret="asdawwewefrtyh2f0a32eb7e1731e85d",' .
            'oauth_token="7c0709f789e1f38a17aa4b9a28e1b06c",' .
            'oauth_token_secret="a6agsfrsfgsrjjjjyy487939244ssggg",' .
            'oauth_signature="valid_signature"';

        $this->assertEquals($expectedHeader, $oauthHeader, 'Generated Oauth header is incorrect');
    }

    /**
     * @dataProvider dataProviderMissingParamForBuildAuthorizationHeaderTest
     */
    public function testMissingParamForBuildAuthorizationHeader($expectedMessage, $request)
    {
        $this->setExpectedException(
            '\Magento\Framework\Oauth\OauthInputException',
            $expectedMessage,
            0
        );

        $requestUrl = 'http://www.example.com/endpoint';
        $this->_oauth->buildAuthorizationHeader($request, $requestUrl);
    }

    public function dataProviderMissingParamForBuildAuthorizationHeaderTest()
    {
        return array(
            array(
                'oauth_consumer_key',
                array( //'oauth_consumer_key' => 'edf957ef88492f0a32eb7e1731e85d',
                    'oauth_consumer_secret' => 'asdawwewefrtyh2f0a32eb7e1731e85d',
                    'oauth_token' => '7c0709f789e1f38a17aa4b9a28e1b06c',
                    'oauth_token_secret' => 'a6agsfrsfgsrjjjjyy487939244ssggg',
                    'custom_param1' => 'foo',
                    'custom_param2' => 'bar'
                )
            ),
            array(
                'oauth_consumer_secret',
                array(
                    'oauth_consumer_key' => 'edf957ef88492f0a32eb7e1731e85d',
                    //'oauth_consumer_secret' => 'asdawwewefrtyh2f0a32eb7e1731e85d',
                    'oauth_token' => '7c0709f789e1f38a17aa4b9a28e1b06c',
                    'oauth_token_secret' => 'a6agsfrsfgsrjjjjyy487939244ssggg',
                    'custom_param1' => 'foo',
                    'custom_param2' => 'bar'
                )
            ),
            array(
                'oauth_token',
                array(
                    'oauth_consumer_key' => 'edf957ef88492f0a32eb7e1731e85d',
                    'oauth_consumer_secret' => 'asdawwewefrtyh2f0a32eb7e1731e85d',
                    //'oauth_token' => '7c0709f789e1f38a17aa4b9a28e1b06c',
                    'oauth_token_secret' => 'a6agsfrsfgsrjjjjyy487939244ssggg',
                    'custom_param1' => 'foo',
                    'custom_param2' => 'bar'
                )
            ),
            array(
                'oauth_token_secret',
                array(
                    'oauth_consumer_key' => 'edf957ef88492f0a32eb7e1731e85d',
                    'oauth_consumer_secret' => 'asdawwewefrtyh2f0a32eb7e1731e85d',
                    'oauth_token' => '7c0709f789e1f38a17aa4b9a28e1b06c',
                    //'oauth_token_secret' => 'a6agsfrsfgsrjjjjyy487939244ssggg',
                    'custom_param1' => 'foo',
                    'custom_param2' => 'bar'
                )
            )
        );
    }

    protected function _getAccessTokenRequiredParams($amendments = array())
    {
        $requiredParams = array(
            'oauth_consumer_key' => $this->_generateRandomString(
                    \Magento\Framework\Oauth\Helper\Oauth::LENGTH_CONSUMER_KEY
                ),
            'oauth_signature' => '',
            'oauth_signature_method' => \Magento\Framework\Oauth\OauthInterface::SIGNATURE_SHA1,
            'oauth_nonce' => '',
            'oauth_timestamp' => (string)time(),
            'oauth_token' => $this->_generateRandomString(\Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN),
            'oauth_verifier' => $this->_oauthVerifier
        );

        return array_merge($requiredParams, $amendments);
    }

    private function _generateRandomString($length)
    {
        return substr(
            str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)),
            0,
            $length
        );
    }
}
