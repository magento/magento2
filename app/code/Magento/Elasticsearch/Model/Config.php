<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Search\Model\EngineResolver;
use Magento\Store\Model\ScopeInterface;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Framework\App\ObjectManager;

/**
 * Elasticsearch config model
 * @api
 * @since 100.1.0
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
     * @since 100.1.0
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var ClientResolver
     */
    private $clientResolver;

    /**
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * Available Elasticsearch engines.
     *
     * @var array
     */
    private $engineList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ClientResolver|null $clientResolver
     * @param EngineResolverInterface|null $engineResolver
     * @param string|null $prefix
     * @param array $engineList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ClientResolver $clientResolver = null,
        EngineResolverInterface $engineResolver = null,
        $prefix = null,
        $engineList = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->clientResolver = $clientResolver ?: ObjectManager::getInstance()->get(ClientResolver::class);
        $this->engineResolver = $engineResolver ?: ObjectManager::getInstance()->get(EngineResolverInterface::class);
        $this->prefix = $prefix ?: $this->clientResolver->getCurrentEngine();
        $this->engineList = $engineList;
    }

    /**
     * @inheritdoc
     *
     * @since 100.1.0
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
     * @since 100.1.0
     */
    public function getElasticsearchConfigData($field, $storeId = null)
    {
        return $this->getSearchConfigData($this->prefix . '_' . $field, $storeId);
    }

    /**
     * Retrieve information from search engine configuration
     *
     * @param string $field
     * @param int|null $storeId
     * @return string|int
     * @since 100.1.0
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
     * @since 100.1.0
     */
    public function isElasticsearchEnabled()
    {
        return in_array($this->engineResolver->getCurrentSearchEngine(), $this->engineList);
    }

    /**
     * Get Elasticsearch index prefix
     *
     * @return string
     * @since 100.1.0
     */
    public function getIndexPrefix()
    {
        return $this->getElasticsearchConfigData('index_prefix');
    }

    /**
     * Get Elasticsearch entity type
     *
     * @return string
     * @since 100.1.0
     */
    public function getEntityType()
    {
        return self::ELASTICSEARCH_TYPE_DOCUMENT;
    }
}
