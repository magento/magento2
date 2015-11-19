<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Elasticsearch config model
 */
class Config implements ClientOptionsInterface
{
    /**
     * Current adapter name
     */
    const ELASTICSEARCH = 'elasticsearch';

    /**
     * Elasticsearch Entity type for product
     */
    const ELASTICSEARCH_TYPE_PRODUCT = 'product';

    /**
     * Default Elasticsearch server timeout
     */
    const ELASTICSEARCH_DEFAULT_TIMEOUT = 15;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var AdapterFactoryInterface
     */
    protected $adapterFactory;

    /**
     * Store result of third party search engine availability check
     *
     * @var bool|null
     */
    protected $isThirdPartyEngineAvailable = null;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param AdapterFactoryInterface $adapterFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        AdapterFactoryInterface $adapterFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareClientOptions($options = [])
    {
        $defaultOptions = [
            'hostname' => $this->getElasticsearchConfigData('server_hostname'),
            'port' => $this->getElasticsearchConfigData('server_port'),
            'index' => $this->getElasticsearchConfigData('index_name'),
            'enableAuth' => $this->getElasticsearchConfigData('enable_auth'),
            'username' => $this->getElasticsearchConfigData('username'),
            'password' => $this->getElasticsearchConfigData('password'),
            'timeout' => $this->getElasticsearchConfigData('server_timeout') ? : self::ELASTICSEARCH_DEFAULT_TIMEOUT,
        ];
        $options = array_merge($defaultOptions, $options);
        return $options;
    }

    /**
     * Retrieve information from Elasticsearch search engine configuration
     *
     * @param string $field
     * @return string|int
     */
    public function getElasticsearchConfigData($field)
    {
        $path = 'catalog/search/elasticsearch_' . $field;
        return $this->scopeConfig->getValue($path);
    }

    /**
     * Retrieve information from search engine configuration
     *
     * @param string $field
     * @param int|null $storeId
     * @return string|int
     */
    public function getSearchConfigData($field, $storeId = null)
    {
        $path = 'catalog/search/' . $field;
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Return true if third party search engine is used
     *
     * @return bool
     */
    public function isEsEnabled()
    {
        return $this->getSearchConfigData('engine') == self::ELASTICSEARCH;
    }

    /**
     * Check if enterprise engine is available
     *
     * @return bool
     */
    public function isActiveEngine()
    {
        return $this->adapterFactory->createAdapter()->ping();
    }

    /**
     * Get Elasticsearch index name
     *
     * @return string
     */
    public function getIndexName()
    {
        return $this->getElasticsearchConfigData('index_name');
    }

    /**
     * get Elasticsearch entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return self::ELASTICSEARCH_TYPE_PRODUCT;
    }

    /**
     * {@inheritdoc}
     */
    public function isThirdPartyEngineAvailable()
    {
        if ($this->isThirdPartyEngineAvailable === null) {
            $this->isThirdPartyEngineAvailable = $this->isEsEnabled() && $this->isActiveEngine();
        }

        return $this->isThirdPartyEngineAvailable;
    }
}
