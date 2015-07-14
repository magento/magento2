<?php
/**
 * Created by PhpStorm.
 * User: aohorodnyk
 * Date: 14.07.2015
 * Time: 9:56
 */
namespace Magento\Search\Model;

use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Search\Api\SearchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Search implements SearchInterface
{
    /**
     * @var Builder
     */
    private $requestBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @var SearchResponseBuilder
     */
    private $searchResponseBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Builder $requestBuilder
     * @param StoreManagerInterface $storeManager
     * @param SearchEngineInterface $searchEngine
     * @param SearchResponseBuilder $searchResponseBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Builder $requestBuilder, StoreManagerInterface $storeManager, SearchEngineInterface $searchEngine, SearchResponseBuilder $searchResponseBuilder, ScopeConfigInterface $scopeConfig)
    {
        $this->requestBuilder = $requestBuilder;
        $this->storeManager = $storeManager;
        $this->searchEngine = $searchEngine;
        $this->searchResponseBuilder = $searchResponseBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $this->requestBuilder->setRequestName($searchCriteria->getRequestName());

        $searchTerm = $searchCriteria->getSearchTerm();
        if (!empty($searchTerm)) {
            $this->requestBuilder->bind('search_term', $searchTerm);
        }

        $storeId = $this->storeManager->getStore(true)->getId();
        $this->requestBuilder->bindDimension('scope', $storeId);

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $this->addFieldToFilter($filter->getField(), $filter->getValue());
            }
        }

        $priceRangeCalculation = $this->scopeConfig->getValue(
            AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            ScopeInterface::SCOPE_STORE
        );
        if ($priceRangeCalculation) {
            $this->requestBuilder->bind('price_dynamic_algorithm', $priceRangeCalculation);
        }

        $this->requestBuilder->setFrom($searchCriteria->getCurrentPage() * $searchCriteria->getPageSize());
        $this->requestBuilder->setSize($searchCriteria->getPageSize());
        $request = $this->requestBuilder->create();
        $searchResponse = $this->searchEngine->search($request);

        return $this->searchResponseBuilder->build($searchResponse)
            ->setSearchCriteria($searchCriteria);
    }

    /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param null $condition
     * @return $this
     */
    private function addFieldToFilter($field, $condition = null)
    {
        if (!is_array($condition) || !in_array(key($condition), ['from', 'to'])) {
            $this->requestBuilder->bind($field, $condition);
        } else {
            if (!empty($condition['from'])) {
                $this->requestBuilder->bind("{$field}.from", $condition['from']);
            }
            if (!empty($condition['to'])) {
                $this->requestBuilder->bind("{$field}.to", $condition['to']);
            }
        }
        return $this;
    }
}
