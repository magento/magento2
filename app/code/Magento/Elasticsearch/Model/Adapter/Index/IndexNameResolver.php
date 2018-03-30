<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @api
 * @since 100.1.0
 */
class IndexNameResolver
{
    /**
     * @var ConnectionManager
     * @since 100.1.0
     */
    protected $connectionManager;

    /**
     * @var Config
     * @since 100.1.0
     */
    protected $clientConfig;

    /**
     * @var ElasticsearchClient
     * @since 100.1.0
     */
    protected $client;

    /**
     * @var LoggerInterface
     * @since 100.1.0
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
                __('The search failed because of a search engine misconfiguration.')
            );
        }
    }

    /**
     * Get index namespace from config
     *
     * @return string
     * @since 100.1.0
     */
    protected function getIndexNamespace()
    {
        return $this->clientConfig->getIndexPrefix();
    }

    /**
     * Get index namespace from config
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     *
     * @return string
     * @since 100.1.0
     */
    public function getIndexNameForAlias($storeId, $mappedIndexerId)
    {
        return $this->clientConfig->getIndexPrefix() . '_' . $mappedIndexerId . '_' . $storeId;
    }

    /**
     * Returns the index name
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @param array $preparedIndex
     * @return string
     * @since 100.1.0
     */
    public function getIndexName($storeId, $mappedIndexerId, array $preparedIndex)
    {
        if (isset($preparedIndex[$storeId])) {
            return $preparedIndex[$storeId];
        } else {
            $indexName = $this->getIndexFromAlias($storeId, $mappedIndexerId);
            if (empty($indexName)) {
                $indexName = $this->getIndexPattern($storeId, $mappedIndexerId) . 1;
            }
        }
        return $indexName;
    }

    /**
     * Returns index pattern.
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return string
     * @since 100.1.0
     */
    public function getIndexPattern($storeId, $mappedIndexerId)
    {
        return $this->getIndexNamespace() . '_' . $mappedIndexerId . '_' . $storeId . '_v';
    }

    /**
     * Returns index for store in alias definition.
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return string
     * @since 100.1.0
     */
    public function getIndexFromAlias($storeId, $mappedIndexerId)
    {
        $storeIndex = '';
        $indexPattern = $this->getIndexPattern($storeId, $mappedIndexerId);
        $namespace = $this->getIndexNamespace() . '_' . $mappedIndexerId . '_' . $storeId;
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
     * @since 100.1.0
     */
    public function getIndexMapping($indexerId)
    {
        if ($indexerId == Fulltext::INDEXER_ID) {
            $mappedIndexerId = 'product';
        } else {
            $mappedIndexerId = $indexerId;
        }
        return $mappedIndexerId;
    }
}
