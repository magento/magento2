<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;

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
     * Elasticsearch Entity type
     */
    const ELASTICSEARCH_TYPE_DOCUMENT = 'document';

    /**
     * Elasticsearch default Entity type
     */
    const ELASTICSEARCH_TYPE_DEFAULT = 'product';

    /**
     * Default Elasticsearch server timeout
     */
    const ELASTICSEARCH_DEFAULT_TIMEOUT = 15;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareClientOptions($options = [])
    {
        $defaultOptions = [
            'hostname' => $this->getElasticsearchConfigData('server_hostname'),
            'port' => $this->getElasticsearchConfigData('server_port'),
            'index' => $this->getElasticsearchConfigData('index_prefix'),
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
     * Get Elasticsearch index prefix
     *
     * @return string
     */
    public function getIndexPrefix()
    {
        return $this->getElasticsearchConfigData('index_prefix');
    }

    /**
     * get Elasticsearch entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return self::ELASTICSEARCH_TYPE_DOCUMENT;
    }
}
