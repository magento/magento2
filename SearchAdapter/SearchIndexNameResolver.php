<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Elasticsearch\Model\Config;

/**
 * Alias name resolver
 */
class SearchIndexNameResolver
{
    /**
     * @var Config
     */
    private $clientConfig;

    /**
     * @param Config $clientConfig
     */
    public function __construct(
        Config $clientConfig
    ) {
        $this->clientConfig = $clientConfig;
    }

    /**
     * Returns the index (alias) name
     *
     * @param int $storeId
     * @param string $indexerId
     * @return string
     */
    public function getIndexName($storeId, $indexerId)
    {
        $mappedIndexerId = $this->getIndexMapping($indexerId);
        return $this->clientConfig->getIndexPrefix() . '_' . $mappedIndexerId . '_' . $storeId;
    }

    /**
     * Get index name by indexer ID
     *
     * @param string $indexerId
     * @return string
     */
    private function getIndexMapping($indexerId)
    {
        if ($indexerId == Fulltext::INDEXER_ID) {
            $mappedIndexerId = 'product';
        } else {
            $mappedIndexerId = $indexerId;
        }
        return $mappedIndexerId;
    }
}
