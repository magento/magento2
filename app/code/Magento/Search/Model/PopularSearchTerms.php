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
     * @var \Magento\Search\Model\ResourceModel\Query
     */
    private $queryResource;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Search\Model\ResourceModel\Query\Collection
     * @param ResourceModel\Query $queryResource
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Search\Model\ResourceModel\Query\Collection $queryCollection,
        \Magento\Search\Model\ResourceModel\Query $queryResource
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->queryCollection = $queryCollection;
        $this->queryResource = $queryResource;
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
        $connection = $this->queryResource->getConnection();
        $select = $connection->select();
        $select->from($this->queryResource->getMainTable(), [$this->queryResource->getIdFieldName()])
            ->where('query_text = ?', $term)
            ->where('store_id = ?', $storeId)
            ->where('num_results > 0')
            ->order(['popularity DESC'])
            ->limit($this->getMaxCountCacheableSearchTerms($storeId));
        $queryId = $connection->fetchOne($select);

        return (bool) $queryId;
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
