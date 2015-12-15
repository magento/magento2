<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Elasticsearch\Model\Config;

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
     * @param array $options
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        Config $clientConfig,
        $options = []
    ) {
        $this->clientConfig = $clientConfig;
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
        return $this->clientConfig->getIndexPrefix() . '_' . $indexName . '_' . $storeId;
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
