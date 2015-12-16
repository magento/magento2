<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Elasticsearch\Model\Config;
use Magento\Framework\Exception\LocalizedException;

/**
 * Alias name resolver
 */
class SearchIndexNameResolver
{
    /**
     * @var Config
     */
    protected $clientConfig;

    /**
     * @var ElasticsearchClient
     */
    protected $client;

    /**
     * Constructor for Index Name Resolver.
     *
     * @param Config $clientConfig
     * @param array $options
     * @throws LocalizedException
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
        $entityType = $this->getIndexMapping($indexerId);
        return $this->clientConfig->getIndexPrefix() . '_' . $entityType . '_' . $storeId;
    }

    /**
     * Get index name by indexer ID
     *
     * @param string $indexerId
     * @return string
     */
    protected function getIndexMapping($indexerId)
    {
        if ($indexerId == Fulltext::INDEXER_ID) {
            $entityType = 'product';
        } else {
            $entityType = $indexerId;
        }
        return $entityType;
    }
}
