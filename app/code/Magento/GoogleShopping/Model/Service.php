<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model;

/**
 * Google Content Item Types Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Service extends \Magento\Framework\Object
{
    /**
     * Client instance identifier in registry
     *
     * @var string
     */
    protected $_clientRegistryId = 'GCONTENT_HTTP_CLIENT';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Config
     *
     * @var \Magento\GoogleShopping\Model\Config
     */
    protected $_config;

    /**
     * Service
     * @var \Magento\Framework\Gdata\Gshopping\Content
     */
    protected $_service;

    /**
     * Content factory
     * @var \Magento\Framework\Gdata\Gshopping\ContentFactory
     */
    protected $_contentFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Registry $coreRegistry
     * @param Config $config
     * @param \Magento\Framework\Gdata\Gshopping\ContentFactory $contentFactory
     * @param array $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\GoogleShopping\Model\Config $config,
        \Magento\Framework\Gdata\Gshopping\ContentFactory $contentFactory,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
        $this->_contentFactory = $contentFactory;
        parent::__construct($data);
    }

    /**
     * Retutn Google Content Client Instance
     *
     * @param int $storeId
     * @param string $loginToken
     * @param string $loginCaptcha
     * @throws \Magento\Framework\Model\Exception On http connection failure
     * @return \Zend_Http_Client
     */
    public function getClient($storeId = null, $loginToken = null, $loginCaptcha = null)
    {
        $user = $this->getConfig()->getAccountLogin($storeId);
        $pass = $this->getConfig()->getAccountPassword($storeId);
        $type = $this->getConfig()->getAccountType($storeId);

        // Create an authenticated HTTP client
        $errorMsg = __(
            'Sorry, but we can\'t connect to Google Content. Please check the account settings in your store configuration.'
        );
        try {
            if (!$this->_coreRegistry->registry($this->_clientRegistryId)) {
                $client = \Zend_Gdata_ClientLogin::getHttpClient(
                    $user,
                    $pass,
                    \Magento\Framework\Gdata\Gshopping\Content::AUTH_SERVICE_NAME,
                    null,
                    '',
                    $loginToken,
                    $loginCaptcha,
                    \Zend_Gdata_ClientLogin::CLIENTLOGIN_URI,
                    $type
                );
                $configTimeout = ['timeout' => 60];
                $client->setConfig($configTimeout);
                $this->_coreRegistry->register($this->_clientRegistryId, $client);
            }
        } catch (\Zend_Gdata_App_CaptchaRequiredException $e) {
            throw $e;
        } catch (\Zend_Gdata_App_HttpException $e) {
            throw new \Magento\Framework\Model\Exception($errorMsg . __('Error: %1', $e->getMessage()));
        } catch (\Zend_Gdata_App_AuthException $e) {
            throw new \Magento\Framework\Model\Exception($errorMsg . __('Error: %1', $e->getMessage()));
        }

        return $this->_coreRegistry->registry($this->_clientRegistryId);
    }

    /**
     * Set Google Content Client Instance
     *
     * @param \Zend_Http_Client $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->_coreRegistry->unregister($this->_clientRegistryId);
        $this->_coreRegistry->register($this->_clientRegistryId, $client);
        return $this;
    }

    /**
     * Return Google Content Service Instance
     *
     * @param int $storeId
     * @return \Magento\Framework\Gdata\Gshopping\Content
     */
    public function getService($storeId = null)
    {
        if (!$this->_service) {
            $this->_service = $this->_connect($storeId);

            if ($this->getConfig()->getIsDebug($storeId)) {
                $this->_service->setLogAdapter($this->logger, 'debug')->setDebug(true);
            }
        }
        return $this->_service;
    }

    /**
     * Set Google Content Service Instance
     *
     * @param \Magento\Framework\Gdata\Gshopping\Content $service
     * @return $this
     */
    public function setService($service)
    {
        $this->_service = $service;
        return $this;
    }

    /**
     * Google Content Config
     *
     * @return \Magento\GoogleShopping\Model\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Authorize Google Account
     *
     * @param int $storeId
     * @return \Magento\Framework\Gdata\Gshopping\Content service
     */
    protected function _connect($storeId = null)
    {
        $accountId = $this->getConfig()->getAccountId($storeId);
        $client = $this->getClient($storeId);
        $service = $this->_contentFactory->create(['client' => $client, 'accountId' => $accountId]);
        return $service;
    }
}
