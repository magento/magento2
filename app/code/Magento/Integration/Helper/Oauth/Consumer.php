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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Integration\Helper\Oauth;

use \Magento\Oauth\OauthInterface;

class Consumer
{
    /** @var  \Magento\Core\Model\StoreManagerInterface */
    protected $_storeManager;

    /** @var  \Magento\Integration\Model\Oauth\Consumer\Factory */
    protected $_consumerFactory;

    /** @var  \Magento\Integration\Model\Oauth\Token\Factory */
    protected $_tokenFactory;

    /** @var  \Magento\Integration\Helper\Oauth\Data */
    protected $_dataHelper;

    /** @var  \Magento\HTTP\ZendClient */
    protected $_httpClient;

    /** @var \Magento\Logger */
    protected $_logger;

    /**
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Integration\Model\Oauth\Consumer\Factory $consumerFactory
     * @param \Magento\Integration\Model\Oauth\Token\Factory $tokenFactory
     * @param \Magento\Integration\Helper\Oauth\Data $dataHelper
     * @param \Magento\HTTP\ZendClient $httpClient
     * @param \Magento\Logger $logger
     */
    public function __construct(
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Integration\Model\Oauth\Consumer\Factory $consumerFactory,
        \Magento\Integration\Model\Oauth\Token\Factory $tokenFactory,
        \Magento\Integration\Helper\Oauth\Data $dataHelper,
        \Magento\HTTP\ZendClient $httpClient,
        \Magento\Logger $logger
    ) {
        $this->_storeManager = $storeManager;
        $this->_consumerFactory = $consumerFactory;
        $this->_tokenFactory = $tokenFactory;
        $this->_dataHelper = $dataHelper;
        $this->_httpClient = $httpClient;
        $this->_logger = $logger;
    }

    /**
     * Create a new consumer account when an integration is installed.
     *
     * @param array $consumerData - Information provided by an integration when the integration is installed.
     * <pre>
     * array(
     *     'name' => 'Integration Name',
     *     'key' => 'a6aa81cc3e65e2960a4879392445e718',
     *     'secret' => 'b7bb92dd4f76f3a71b598a4a3556f829'
     * )
     * </pre>
     * @return array - The integration (consumer) data.
     * @throws \Magento\Core\Exception
     * @throws \Magento\Oauth\Exception
     */
    public function createConsumer($consumerData)
    {
        try {
            $consumer = $this->_consumerFactory->create($consumerData);
            $consumer->save();
            return $consumer->getData();
        } catch (\Magento\Core\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Oauth\Exception(__('Unexpected error. Unable to create OAuth Consumer account.'));
        }
    }

    /**
     * Execute post to integration (consumer) HTTP Post URL. Generate and return oauth_verifier.
     *
     * @param int $consumerId - The consumer Id.
     * @param string $endpointUrl - The integration endpoint Url (for HTTP Post)
     * @return string - The oauth_verifier.
     * @throws \Magento\Core\Exception
     * @throws \Magento\Oauth\Exception
     */
    public function postToConsumer($consumerId, $endpointUrl)
    {
        try {
            $consumer = $this->_consumerFactory->create()->load($consumerId);
            if (!$consumer->getId()) {
                throw new \Magento\Oauth\Exception(
                    __('A consumer with ID %1 does not exist', $consumerId), OauthInterface::ERR_PARAMETER_REJECTED);
            }
            $consumerData = $consumer->getData();
            $verifier = $this->_tokenFactory->create()->createVerifierToken($consumerId);
            $storeBaseUrl = $this->_storeManager->getStore()->getBaseUrl();
            $this->_httpClient->setUri($endpointUrl);
            $this->_httpClient->setParameterPost(
                array(
                    'oauth_consumer_key' => $consumerData['key'],
                    'oauth_consumer_secret' => $consumerData['secret'],
                    'store_base_url' => $storeBaseUrl,
                    'oauth_verifier' => $verifier->getVerifier()
                )
            );
            $maxredirects = $this->_dataHelper->getConsumerPostMaxRedirects();
            $timeout = $this->_dataHelper->getConsumerPostTimeout();
            $this->_httpClient->setConfig(array('maxredirects' => $maxredirects, 'timeout' => $timeout));
            $this->_httpClient->request(\Magento\HTTP\ZendClient::POST);
            return $verifier->getVerifier();
        } catch (\Magento\Core\Exception $exception) {
            throw $exception;
        } catch (\Magento\Oauth\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->_logger->logException($exception);
            throw new \Magento\Oauth\Exception(__('Unable to post data to consumer due to an unexpected error'));
        }
    }
}
