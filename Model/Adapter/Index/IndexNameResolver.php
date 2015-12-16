<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Config;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogSearch\Model\Indexer\Fulltext;

/**
 * Index name resolver
 */
class IndexNameResolver
{
    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var Config
     */
    protected $clientConfig;

    /**
     * @var ElasticsearchClient
     */
    protected $client;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor for Index Name Resolver
     *
     * @param ConnectionManager $connectionManager
     * @param Config $clientConfig
     * @param LoggerInterface $logger
     * @param array $options
     * @throws LocalizedException
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Config $clientConfig,
        LoggerInterface $logger,
        $options = []
    ) {
        $this->connectionManager = $connectionManager;
        $this->clientConfig = $clientConfig;
        $this->logger = $logger;

        try {
            $this->client = $this->connectionManager->getConnection($options);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                __('We were unable to perform the search because of a search engine misconfiguration.')
            );
        }
    }

    /**
     * Get index namespace from config
     *
     * @return string
     */
    protected function getIndexNamespace()
    {
        return $this->clientConfig->getIndexPrefix();
    }

    /**
     * Get index namespace from config
     *
     * @param int $storeId
     * @param string $entityType
     *
     * @return string
     */
    public function getIndexNameForAlias($storeId, $entityType)
    {
        return $this->clientConfig->getIndexPrefix() . '_' . $entityType . '_' . $storeId;
    }

    /**
     * Returns the index name
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
        return $this->getIndexNamespace() . '_' . $entityType . '_' . $storeId . '_v';
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
        $namespace = $this->getIndexNamespace() . '_' . $entityType . '_' . $storeId;
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
    }

    /**
     * Taking index name by indexer ID
     *
     * @param string $indexerId
     * @return string
     */
    public function getIndexMapping($indexerId)
    {
        if ($indexerId == Fulltext::INDEXER_ID) {
            $indexName = 'product';
        } else {
            $indexName = $indexerId;
        }
        return $indexName;
    }
}
