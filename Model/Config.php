<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Elasticsearch\Model\Adapter\ElasticsearchFactory;

/**
 * Elasticsearch config model
 */
class Config implements ClientOptionsInterface
{
    /**
     * Search engine name
     */
    const ENGINE_NAME = 'elasticsearch';

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
     * @var ElasticsearchFactory
     */
    protected $adapterFactory;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param ElasticsearchFactory $adapterFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        ElasticsearchFactory $adapterFactory
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
            'index' => $this->getElasticsearchConfigData('indexer_prefix'),
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
     * @param int $storeId
     * @return string|int
     */
    public function getElasticsearchConfigData($field, $storeId = null)
    {
        return $this->getSearchConfigData('elasticsearch_' . $field, $storeId);
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
    public function isElasticsearchEnabled()
    {
        return $this->getSearchConfigData('engine') == self::ENGINE_NAME;
    }

    /**
     * Get Elasticsearch indexer prefix
     *
     * @return string
     */
    public function getIndexerPrefix()
    {
        return $this->getElasticsearchConfigData('indexer_prefix');
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
}
