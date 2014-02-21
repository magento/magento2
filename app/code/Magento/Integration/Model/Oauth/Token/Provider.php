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

namespace Magento\Integration\Model\Oauth\Token;

use Magento\Oauth\OauthInterface;
use Magento\Oauth\TokenProviderInterface;

class Provider implements TokenProviderInterface
{
    /**
     * @var \Magento\Integration\Model\Oauth\Consumer\Factory
     */
    protected $_consumerFactory;

    /**
     * @var \Magento\Integration\Model\Oauth\Token\Factory
     */
    protected $_tokenFactory;

    /**
     * @var  \Magento\Integration\Helper\Oauth\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Core\Model\Date
     */
    protected $_date;

    /**
     * @param \Magento\Integration\Model\Oauth\Consumer\Factory $consumerFactory
     * @param \Magento\Integration\Model\Oauth\Token\Factory $tokenFactory
     * @param \Magento\Integration\Helper\Oauth\Data $dataHelper
     * @param \Magento\Core\Model\Date $date
     */
    public function __construct(
        \Magento\Integration\Model\Oauth\Consumer\Factory $consumerFactory,
        \Magento\Integration\Model\Oauth\Token\Factory $tokenFactory,
        \Magento\Integration\Helper\Oauth\Data $dataHelper,
        \Magento\Core\Model\Date $date
    ) {
        $this->_consumerFactory = $consumerFactory;
        $this->_tokenFactory = $tokenFactory;
        $this->_dataHelper = $dataHelper;
        $this->_date = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function validateConsumer($consumer)
    {
        // Must use consumer within expiration period.
        $consumerTS = strtotime($consumer->getCreatedAt());
        $expiry = $this->_dataHelper->getConsumerExpirationPeriod();
        if ($this->_date->timestamp() - $consumerTS > $expiry) {
            throw new \Magento\Oauth\Exception(
                __('Consumer key has expired'), OauthInterface::ERR_CONSUMER_KEY_INVALID);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequestToken($consumer)
    {
        $token = $this->getTokenByConsumerId($consumer->getId());
        if ($token->getType() != \Magento\Integration\Model\Oauth\Token::TYPE_VERIFIER) {
            throw new \Magento\Oauth\Exception(
                __('Cannot create request token because consumer token is not a verifier token'),
                OauthInterface::ERR_TOKEN_REJECTED
            );
        }
        $requestToken = $token->createRequestToken($token->getId(), $consumer->getCallbackUrl());
        return array('oauth_token' => $requestToken->getToken(), 'oauth_token_secret' => $requestToken->getSecret());
    }

    /**
     * {@inheritdoc}
     */
    public function validateRequestToken($requestToken, $consumer, $oauthVerifier)
    {
        $token = $this->_getToken($requestToken);

        if (!$this->_isTokenAssociatedToConsumer($token, $consumer)) {
            throw new \Magento\Oauth\Exception(
                __('Request token is not associated with the specified consumer'), OauthInterface::ERR_TOKEN_REJECTED);
        }

        // The pre-auth token has a value of "request" in the type when it is requested and created initially.
        // In this flow (token flow) the token has to be of type "request" else its marked as reused.
        if (\Magento\Integration\Model\Oauth\Token::TYPE_REQUEST != $token->getType()) {
            throw new \Magento\Oauth\Exception(__('Token is already being used'), OauthInterface::ERR_TOKEN_USED);
        }

        $this->_validateVerifierParam($oauthVerifier, $token->getVerifier());

        return $token->getSecret();
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($consumer)
    {
        /** TODO: log the request token in dev mode since its not persisted. */
        $token = $this->getTokenByConsumerId($consumer->getId());
        if (\Magento\Integration\Model\Oauth\Token::TYPE_REQUEST != $token->getType()) {
            throw new \Magento\Oauth\Exception(
                __('Cannot get access token because consumer token is not a request token'));
        }
        $accessToken = $token->convertToAccess();
        return array('oauth_token' => $accessToken->getToken(), 'oauth_token_secret' => $accessToken->getSecret());
    }

    /**
     * {@inheritdoc}
     */
    public function validateAccessTokenRequest($accessToken, $consumer)
    {
        $token = $this->_getToken($accessToken);

        if (!$this->_isTokenAssociatedToConsumer($token, $consumer)) {
            throw new \Magento\Oauth\Exception(
                __('Token is not associated with the specified consumer'), OauthInterface::ERR_TOKEN_REJECTED);
        }
        if (\Magento\Integration\Model\Oauth\Token::TYPE_ACCESS != $token->getType()) {
            throw new \Magento\Oauth\Exception(
                __('Token is not an access token'), OauthInterface::ERR_TOKEN_REJECTED);
        }
        if ($token->getRevoked()) {
            throw new \Magento\Oauth\Exception(__('Access token has been revoked'), OauthInterface::ERR_TOKEN_REVOKED);
        }

        return $token->getSecret();
    }

    /**
     * {@inheritdoc}
     */
    public function validateAccessToken($accessToken)
    {
        $token = $this->_getToken($accessToken);
        // Make sure a consumer is associated with the token.
        $this->_getConsumer($token->getConsumerId());

        if (\Magento\Integration\Model\Oauth\Token::TYPE_ACCESS != $token->getType()) {
            throw new \Magento\Oauth\Exception(__('Token is not an access token'), OauthInterface::ERR_TOKEN_REJECTED);
        }

        if ($token->getRevoked()) {
            throw new \Magento\Oauth\Exception(
                __('Access token has been revoked'), OauthInterface::ERR_TOKEN_REVOKED);
        }

        return $token->getConsumerId();
    }

    /**
     * {@inheritdoc}
     */
    public function validateOauthToken($oauthToken)
    {
        return strlen($oauthToken) == \Magento\Oauth\Helper\Oauth::LENGTH_TOKEN;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumerByKey($consumerKey)
    {
        if (strlen($consumerKey) != \Magento\Oauth\Helper\Oauth::LENGTH_CONSUMER_KEY) {
            throw new \Magento\Oauth\Exception(
                __('Consumer key is not the correct length'), OauthInterface::ERR_CONSUMER_KEY_REJECTED);
        }

        $consumer = $this->_consumerFactory->create()->loadByKey($consumerKey);

        if (!$consumer->getId()) {
            throw new \Magento\Oauth\Exception(
                __('A consumer having the specified key does not exist'), OauthInterface::ERR_CONSUMER_KEY_REJECTED);
        }

        return $consumer;
    }

    /**
     * Validate 'oauth_verifier' parameter.
     *
     * @param string $oauthVerifier
     * @param string $tokenVerifier
     * @return void
     * @throws \Magento\Oauth\Exception
     */
    protected function _validateVerifierParam($oauthVerifier, $tokenVerifier)
    {
        if (!is_string($oauthVerifier)) {
            throw new \Magento\Oauth\Exception(__('Verifier is invalid'), OauthInterface::ERR_VERIFIER_INVALID);
        }
        if (!$this->validateOauthToken($oauthVerifier)) {
            throw new \Magento\Oauth\Exception(
                __('Verifier is not the correct length'), OauthInterface::ERR_VERIFIER_INVALID);
        }
        if ($tokenVerifier != $oauthVerifier) {
            throw new \Magento\Oauth\Exception(
                __('Token verifier and verifier token do not match'), OauthInterface::ERR_VERIFIER_INVALID);
        }
    }

    /**
     * Get consumer by consumer_id for a given token.
     *
     * @param $consumerId
     * @return \Magento\Oauth\ConsumerInterface
     * @throws \Magento\Oauth\Exception
     */
    protected function _getConsumer($consumerId)
    {
        $consumer = $this->_consumerFactory->create()->load($consumerId);

        if (!$consumer->getId()) {
            throw new \Magento\Oauth\Exception(
                __('A consumer with the ID %1 does not exist', $consumerId), OauthInterface::ERR_TOKEN_REJECTED);
        }

        return $consumer;
    }

    /**
     * Load token object and validate it.
     *
     * @param string $token
     * @return \Magento\Integration\Model\Oauth\Token
     * @throws \Magento\Oauth\Exception
     */
    protected function _getToken($token)
    {
        if (!$this->validateOauthToken($token)) {
            throw new \Magento\Oauth\Exception(
                __('Token is not the correct length'), OauthInterface::ERR_TOKEN_REJECTED);
        }

        $tokenObj = $this->_tokenFactory->create()->load($token, 'token');

        if (!$tokenObj->getId()) {
            throw new \Magento\Oauth\Exception(
                __('Specified token does not exist'), OauthInterface::ERR_TOKEN_REJECTED);
        }

        return $tokenObj;
    }

    /**
     * Load token object given a consumer Id.
     *
     * @param int $consumerId - The Id of the consumer.
     * @return \Magento\Integration\Model\Oauth\Token
     * @throws \Magento\Oauth\Exception
     */
    public function getTokenByConsumerId($consumerId)
    {
        $token = $this->_tokenFactory->create()->load($consumerId, 'consumer_id');

        if (!$token->getId()) {
            throw new \Magento\Oauth\Exception(
                __('A token with consumer ID %1 does not exist', $consumerId), OauthInterface::ERR_TOKEN_REJECTED);
        }

        return $token;
    }

    /**
     * Check if token belongs to the same consumer.
     *
     * @param $token \Magento\Integration\Model\Oauth\Token
     * @param $consumer \Magento\Oauth\ConsumerInterface
     * @return boolean
     */
    protected function _isTokenAssociatedToConsumer($token, $consumer)
    {
        return $token->getConsumerId() == $consumer->getId();
    }
}
