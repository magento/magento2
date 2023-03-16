<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\ResourceModel\Query\Collection as QueryCollection;
use Magento\Store\Model\ScopeInterface;

/**
 * Finds top search results in search
 */
class PopularSearchTerms
{
    const XML_PATH_MAX_COUNT_CACHEABLE_SEARCH_TERMS = 'catalog/search/max_count_cacheable_search_terms';

    /**
     * @param ScopeConfigInterface $scopeConfig Scope configuration
     * @param QueryCollection $queryCollection Catalog search data
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly QueryCollection $queryCollection
    ) {
    }

    /**
     * Check if is cacheable search term
     *
     * @param string $term
     * @param int $storeId
     * @return bool
     */
    public function isCacheable(string $term, int $storeId)
    {
        $maxCountCacheableSearchTerms = $this->getMaxCountCacheableSearchTerms($storeId);
        return $this->queryCollection->isTopSearchResult($term, $storeId, $maxCountCacheableSearchTerms);
    }

    /**
     * Retrieve maximum count cacheable search terms
     *
     * @param int $storeId
     * @return int
     */
    private function getMaxCountCacheableSearchTerms(int $storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAX_COUNT_CACHEABLE_SEARCH_TERMS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
