<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Framework\Oauth\Helper\Oauth as OauthHelper;
use Magento\Integration\Helper\Oauth\Data as IntegrationOauthHelper;
use Magento\Integration\Model\Oauth\Consumer as ConsumerModel;
use Magento\Integration\Model\Oauth\ConsumerFactory;
use Magento\Integration\Model\Oauth\Token as OauthTokenModel;
use Magento\Integration\Model\Oauth\TokenFactory as TokenFactory;
use Magento\Integration\Model\Oauth\Token\Provider as TokenProvider;
use Magento\Framework\Exception\IntegrationException;

/**
 * Integration oAuth service.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OauthService implements \Magento\Integration\Api\OauthServiceInterface
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
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $_dateHelper;

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
     * The getter function to get the new DateTime dependency
     *
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     *
     * @deprecated 100.0.6
     */
    private function getDateHelper()
    {
        if ($this->_dateHelper === null) {
            $this->_dateHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        }
        return $this->_dateHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function createConsumer($consumerData)
    {
        try {
            $consumerData['key'] = $this->_oauthHelper->generateConsumerKey();
            $consumerData['secret'] = $this->_oauthHelper->generateConsumerSecret();
            $consumer = $this->_consumerFactory->create()->setData($consumerData);
            $consumer->save();
            return $consumer;
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Oauth\Exception(
                __(
                    "The oAuth consumer account couldn't be created due to an unexpected error. Please try again later."
                )
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
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Oauth\Exception(
                __("The oAuth consumer account couldn't be loaded due to an unexpected error. Please try again later.")
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
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Oauth\Exception(
                __("The oAuth consumer account couldn't be loaded due to an unexpected error. Please try again later.")
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postToConsumer($consumerId, $endpointUrl)
    {
        try {
            $consumer = $this->loadConsumer($consumerId);
            $consumer->setUpdatedAt($this->getDateHelper()->gmtDate());
            $consumer->save();
            if (!$consumer->getId()) {
                throw new \Magento\Framework\Oauth\Exception(
                    __('A consumer with "%1" ID doesn\'t exist. Verify the ID and try again.', $consumerId)
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
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            throw $exception;
        } catch (\Magento\Framework\Oauth\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->_logger->critical($exception);
            throw new \Magento\Framework\Oauth\Exception(
                __('The attempt to post data to consumer failed due to an unexpected error. Please try again later.')
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
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    protected function _loadConsumerById($consumerId)
    {
        $consumer = $this->_consumerFactory->create()->load($consumerId);
        if (!$consumer->getId()) {
            throw new IntegrationException(
                __('A consumer with ID "%1" doesn\'t exist. Verify the ID and try again.', $consumerId)
            );
        }
        return $consumer;
    }
}
