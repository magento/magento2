<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\DataProvider\Product\SearchCriteria\Builder;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\SearchStorefrontApi\Api\Data\ProductSearchRequestInterface;

/**
 * Class RequestTypeApplier
 */
class RequestTypeApplier extends FilterApplier
{
    const REQUEST_WITH_AGGREGATION = 'search_service_request';
    const REQUEST_WITHOUT_AGGREGATION = 'search_service_request_without_aggregation';
    const PRICE_AGGREGATION_ALGORITHM_FIELD = 'price_dynamic_algorithm';
    const XML_PATH_RANGE_CALCULATION = 'catalog/layered_navigation/price_range_calculation';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * RequestNameApplier constructor.
     *
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($filterBuilder, $filterGroupBuilder);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Apply search request name to search criteria.
     *
     * @param ProductSearchRequestInterface $request
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface
     */
    public function apply(
        ProductSearchRequestInterface $request,
        SearchCriteriaInterface $searchCriteria
    ) : SearchCriteriaInterface {
        if ($request->getIncludeAggregations()) {
            $this->preparePriceAggregation($searchCriteria, (int)$request->getStore());
            $requestName = self::REQUEST_WITH_AGGREGATION;
        } else {
            $requestName = self::REQUEST_WITHOUT_AGGREGATION;
        }

        $searchCriteria->setRequestName($requestName);

        return $searchCriteria;
    }

    /**
     * Prepare price aggregation algorithm
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param int $store
     * @return SearchCriteriaInterface
     */
    private function preparePriceAggregation(SearchCriteriaInterface $searchCriteria, int $store): SearchCriteriaInterface
    {
        // config need to be set in deployment configuration files
        $priceRangeCalculation = $this->scopeConfig->getValue(
            self::XML_PATH_RANGE_CALCULATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($priceRangeCalculation) {
            $searchCriteria = $this->addFilter(
                $searchCriteria,
                self::PRICE_AGGREGATION_ALGORITHM_FIELD,
                $priceRangeCalculation
            );
        }

        return $searchCriteria;
    }
}
