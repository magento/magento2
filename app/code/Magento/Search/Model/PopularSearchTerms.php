<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;

/**
 * Popular search terms
 */
class PopularSearchTerms
{
    const XML_PATH_MAX_COUNT_CACHEABLE_SEARCH_TERMS = 'catalog/search/max_count_cacheable_search_terms';

    /**
     * Scope configuration
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Catalog search data
     *
     * @var \Magento\Search\Model\ResourceModel\Query\Collection
     */
    private $queryCollection;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Search\Model\ResourceModel\Query\Collection
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Search\Model\ResourceModel\Query\Collection $queryCollection
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->queryCollection = $queryCollection;
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
        $terms = $this->queryCollection
            ->setPopularQueryFilter($storeId)
            ->setPageSize($this->getMaxCountCacheableSearchTerms($storeId))
            ->load()
            ->getColumnValues('query_text');

        return in_array($term, $terms);
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
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
