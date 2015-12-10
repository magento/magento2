<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;

/**
 * Index name resolver
 */
class IndexNameResolver
{
    /**
     * @var \Magento\Elasticsearch\Model\Config
     */
    protected $clientConfig;

    /**
     * @var ElasticsearchClient
     */
    protected $client;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor for Index Name Resolver.
     *
     * @param \Magento\Elasticsearch\Model\Config $clientConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $options
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ConnectionManager $connectionManager,
        \Magento\Elasticsearch\Model\Config $clientConfig,
        \Psr\Log\LoggerInterface $logger,
        $options = []
    ) {
        $this->connectionManager = $connectionManager;
        $this->clientConfig = $clientConfig;
        $this->logger = $logger;

        try {
            $this->client = $this->connectionManager->getConnection($options);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We were unable to perform the search because of a search engine misconfiguration.')
            );
        }
    }

    /**
     * Get index namespace from config.
     *
     * @return string
     */
    public function getIndexNamespace()
    {
        return $this->clientConfig->getIndexerPrefix();
    }

    /**
     * Returns the index name.
     *
     * @param int $storeId
     * @param string $entityType
     * @param array $preparedIndex
     * @return string
     */
    public function getIndexName($storeId, $entityType, array $preparedIndex)
    {
        if (isset($preparedIndex[$storeId])) {
            return $preparedIndex[$storeId];
        } else {
            $indexName = $this->getIndexFromAlias($storeId, $entityType);
            if (empty($indexName)) {
                $indexName = $this->getIndexPattern($storeId, $entityType) . 1;
            }
        }
        return $indexName;
    }

    /**
     * Returns index pattern.
     *
     * @param int $storeId
     * @param string $entityType
     * @return string
     */
    public function getIndexPattern($storeId, $entityType)
    {
        return $this->getIndexNamespace() .'_'. $entityType . '_' . $storeId . '_v';
    }

    /**
     * Returns index for store in alias definition.
     *
     * @param int $storeId
     * @param string $entityType
     * @return string
     */
    public function getIndexFromAlias($storeId, $entityType)
    {
        $storeIndex = '';
        $indexPattern = $this->getIndexPattern($storeId, $entityType);
        $namespace = $this->getIndexNamespace();
        if ($this->client->existsAlias($namespace)) {
            $alias = $this->client->getAlias($namespace);
            $indices = array_keys($alias);
            foreach ($indices as $index) {
                if (strpos($index, $indexPattern) === 0) {
                    $storeIndex = $index;
                    break;
                }
            }
        }
        return $storeIndex;
    }}