<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Service\V1;

use Magento\Framework\Oauth\Helper\Oauth as OauthHelper;
use Magento\Integration\Helper\Oauth\Data as IntegrationOauthHelper;
use Magento\Integration\Model\Oauth\Consumer as ConsumerModel;
use Magento\Integration\Model\Oauth\Consumer\Factory as ConsumerFactory;
use Magento\Integration\Model\Oauth\Token as OauthTokenModel;
use Magento\Integration\Model\Oauth\TokenFactory as TokenFactory;
use Magento\Integration\Model\Oauth\Token\Provider as TokenProvider;

/**
 * Integration oAuth service.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Oauth implements OauthInterface
{
    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var  ConsumerFactory
     */
    protected $_consumerFactory;

    /**
     * @var  TokenFactory
     */
    protected $_tokenFactory;

    /**
     * @var  IntegrationOauthHelper
     */
    protected $_dataHelper;

    /**
     * @var  \Magento\Framework\HTTP\ZendClient
     */
    protected $_httpClient;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var OauthHelper
     */
    protected $_oauthHelper;

    /**
     * @var TokenProvider
     */
    protected $_tokenProvider;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ConsumerFactory $consumerFactory
     * @param TokenFactory $tokenFactory
     * @param IntegrationOauthHelper $dataHelper
     * @param \Magento\Framework\HTTP\ZendClient $httpClient
     * @param \Psr\Log\LoggerInterface $logger
     * @param OauthHelper $oauthHelper
     * @param TokenProvider $tokenProvider
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ConsumerFactory $consumerFactory,
        TokenFactory $tokenFactory,
        IntegrationOauthHelper $dataHelper,
        \Magento\Framework\HTTP\ZendClient $httpClient,
        \Psr\Log\LoggerInterface $logger,
        OauthHelper $oauthHelper,
        TokenProvider $tokenProvider
    ) {
        $this->_storeManager = $storeManager;
        $this->_consumerFactory = $consumerFactory;
        $this->_tokenFactory = $tokenFactory;
        $this->_dataHelper = $dataHelper;
        $this->_httpClient = $httpClient;
        $this->_logger = $logger;
        $this->_oauthHelper = $oauthHelper;
        $this->_tokenProvider = $tokenProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function createConsumer($consumerData)
    {
        try {
            $consumerData['key'] = $this->_oauthHelper->generateConsumerKey();
            $consumerData['secret'] = $this->_oauthHelper->generateConsumerSecret();
            $consumer = $this->_consumerFactory->create($consumerData);
            $consumer->save();
            return $consumer;
        } catch (\Magento\Framework\Model\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Oauth\Exception(
                'Unexpected error. Unable to create oAuth consumer account.'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createAccessToken($consumerId, $clearExistingToken = false)
    {
        try {
            $consumer = $this->_consumerFactory->create()->load($consumerId);
            $existingToken = $this->_tokenProvider->getIntegrationTokenByConsumerId($consumer->getId());
            if ($existingToken && $clearExistingToken) {
                $existingToken->delete();
                unset($existingToken);
            }
        } catch (\Exception $e) {
        }
        if (!isset($existingToken)) {
            $consumer = $this->_consumerFactory->create()->load($consumerId);
            $this->_tokenFactory->create()->createVerifierToken($consumerId);
            $this->_tokenProvider->createRequestToken($consumer);
            $this->_tokenProvider->getAccessToken($consumer);
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($consumerId)
    {
        try {
            $consumer = $this->_consumerFactory->create()->load($consumerId);
            $token = $this->_tokenProvider->getIntegrationTokenByConsumerId($consumer->getId());
            if ($token->getType() != OauthTokenModel::TYPE_ACCESS) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function loadConsumer($consumerId)
    {
        try {
            return $this->_consumerFactory->create()->load($consumerId);
        } catch (\Magento\Framework\Model\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Oauth\Exception(
                'Unexpected error. Unable to load oAuth consumer account.'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadConsumerByKey($key)
    {
        try {
            return $this->_consumerFactory->create()->load($key, 'key');
        } catch (\Magento\Framework\Model\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Oauth\Exception(
                'Unexpected error. Unable to load oAuth consumer account.'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postToConsumer($consumerId, $endpointUrl)
    {
        try {
            $consumer = $this->_consumerFactory->create()->load($consumerId);
            if (!$consumer->getId()) {
                throw new \Magento\Framework\Oauth\Exception(
                    __('A consumer with ID %1 does not exist', $consumerId),
                    OauthInterface::ERR_PARAMETER_REJECTED
                );
            }
            $consumerData = $consumer->getData();
            $verifier = $this->_tokenFactory->create()->createVerifierToken($consumerId);
            $storeBaseUrl = $this->_storeManager->getStore()->getBaseUrl();
            $this->_httpClient->setUri($endpointUrl);
            $this->_httpClient->setParameterPost(
                [
                    'oauth_consumer_key' => $consumerData['key'],
                    'oauth_consumer_secret' => $consumerData['secret'],
                    'store_base_url' => $storeBaseUrl,
                    'oauth_verifier' => $verifier->getVerifier(),
                ]
            );
            $maxredirects = $this->_dataHelper->getConsumerPostMaxRedirects();
            $timeout = $this->_dataHelper->getConsumerPostTimeout();
            $this->_httpClient->setConfig(['maxredirects' => $maxredirects, 'timeout' => $timeout]);
            $this->_httpClient->request(\Magento\Framework\HTTP\ZendClient::POST);
            return $verifier->getVerifier();
        } catch (\Magento\Framework\Model\Exception $exception) {
            throw $exception;
        } catch (\Magento\Framework\Oauth\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->_logger->critical($exception);
            throw new \Magento\Framework\Oauth\Exception(
                'Unable to post data to consumer due to an unexpected error'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteConsumer($consumerId)
    {
        $consumer = $this->_loadConsumerById($consumerId);
        $data = $consumer->getData();
        $consumer->delete();
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIntegrationToken($consumerId)
    {
        try {
            $consumer = $this->_consumerFactory->create()->load($consumerId);
            $existingToken = $this->_tokenProvider->getIntegrationTokenByConsumerId($consumer->getId());
            $existingToken->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Load consumer by id.
     *
     * @param int $consumerId
     * @return ConsumerModel
     * @throws \Magento\Integration\Exception
     */
    protected function _loadConsumerById($consumerId)
    {
        $consumer = $this->_consumerFactory->create()->load($consumerId);
        if (!$consumer->getId()) {
            throw new \Magento\Integration\Exception(__("Consumer with ID '%1' does not exist.", $consumerId));
        }
        return $consumer;
    }
}
