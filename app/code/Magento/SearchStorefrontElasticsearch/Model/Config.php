<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SearchStorefrontElasticsearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\SearchStorefrontAdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\SearchStorefrontAdvancedSearch\Model\Client\ClientResolver;
use Magento\SearchStorefrontElasticsearch\Api\Data\ConnectionConfigInterface as ConnectionConfig;

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
     * @var ConnectionConfig
     */
    private $config;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ClientResolver $clientResolver
     * @param EngineResolverInterface $engineResolver
     * @param ConnectionConfig $config
     * @param string|null $prefix
     * @param array $engineList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ClientResolver $clientResolver,
        EngineResolverInterface $engineResolver,
        ConnectionConfig $config,
        $prefix = null,
        $engineList = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->clientResolver = $clientResolver;
        $this->engineResolver = $engineResolver;
        $this->prefix = $prefix ?: $this->clientResolver->getCurrentEngine();
        $this->engineList = $engineList;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     *
     * @since 100.1.0
     */
    public function prepareClientOptions($options = [])
    {
        $defaultOptions = [
            'hostname' => $this->config->getServerHostname(),
            'port' => $this->config->getServerPort(),
            'index' => $this->config->getIndexPrefix(),
            'enableAuth' => $this->config->getEnableAuth(),
            'username' => $this->config->getUsername(),
            'password' => $this->config->getPassword(),
            'timeout' => $this->config->getTimeout() ? : self::ELASTICSEARCH_DEFAULT_TIMEOUT,
            'engine' => $this->config->getEngine()
        ];

        $options = array_merge($defaultOptions, $options);
        $allowedOptions = array_keys($defaultOptions);

        return array_filter(
            $options,
            function (string $key) use ($allowedOptions) {
                return in_array($key, $allowedOptions);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Retrieve information from Elasticsearch search engine configuration
     *
     * @param string $field
     * @param int $storeId
     * @return string|int
     * @since 100.1.0
     */
    public function getElasticsearchConfigData($field, $storeId = null) // @TODO scope based config??
    {
        $config = $this->config->getConfig();
        return $config[$field] ?? '';
    }

    /**
     * Return true if third party search engine is used
     *
     * @return bool
     * @since 100.1.0
     */
    public function isElasticsearchEnabled()
    {
        // @TODO elastic will be enabled iin any case for service
        return true;
    }

    /**
     * Get Elasticsearch index prefix
     *
     * @return string
     * @since 100.1.0
     */
    public function getIndexPrefix()
    {
        return $this->config->getIndexPrefix();
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
