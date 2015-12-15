<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\CatalogSearch\Model\Indexer\Fulltext;

/**
 * Alias name resolver
 */
class SearchIndexNameResolver
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
    )
    {
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
     * Returns the index (alias) name.
     *
     * @param int $storeId
     * @param string $indexerId
     * @return string
     */
    public function getIndexName($storeId, $indexerId)
    {
        $indexName = $this->getIndexMapping($indexerId);
        return $this->clientConfig->getIndexerPrefix() . '_' . $indexName . '_' . $storeId;
    }

    /**
     * Taking index name by indexer ID
     *
     * @param string $indexerId
     *
     * @return string
     */
    protected function getIndexMapping($indexerId)
    {
        if ($indexerId == Fulltext::INDEXER_ID) {
            $indexName = 'product';
        } else {
            $indexName = $indexerId;
        }
        return $indexName;
    }
}
