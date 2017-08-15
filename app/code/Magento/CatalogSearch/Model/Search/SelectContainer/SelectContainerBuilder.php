<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search\SelectContainer;

use Magento\CatalogSearch\Model\Search\QueryChecker\FullTextSearchCheck;
use Magento\CatalogSearch\Model\Search\CustomAttributeFilterCheck;
use Magento\CatalogSearch\Model\Search\FiltersExtractor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\ScopeInterface;
use Magento\CatalogSearch\Model\Search\FilterMapper\VisibilityFilter;

/**
 * Class SelectContainerBuilder
 * Class is responsible for SelectContainer creation and filling it with all required data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SelectContainerBuilder
{
    /**
     * @var SelectContainerFactory
     */
    private $selectContainerFactory;

    /**
     * @var FullTextSearchCheck
     */
    private $fullTextSearchCheck;

    /**
     * @var CustomAttributeFilterCheck
     */
    private $customAttributeFilterCheck;

    /**
     * @var FiltersExtractor
     */
    private $filtersExtractor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param SelectContainerFactory $selectContainerFactory
     * @param FullTextSearchCheck $fullTextSearchCheck
     * @param CustomAttributeFilterCheck $customAttributeFilterCheck
     * @param FiltersExtractor $filtersExtractor
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resource
     */
    public function __construct(
        SelectContainerFactory $selectContainerFactory,
        FullTextSearchCheck $fullTextSearchCheck,
        CustomAttributeFilterCheck $customAttributeFilterCheck,
        FiltersExtractor $filtersExtractor,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resource
    ) {
        $this->selectContainerFactory = $selectContainerFactory;
        $this->fullTextSearchCheck = $fullTextSearchCheck;
        $this->customAttributeFilterCheck = $customAttributeFilterCheck;
        $this->filtersExtractor = $filtersExtractor;
        $this->scopeConfig = $scopeConfig;
        $this->resource = $resource;
    }

    /**
     * Builds SelectContainer with all required data
     *
     * @param RequestInterface $request
     * @return SelectContainer
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function buildByRequest(RequestInterface $request)
    {
        $nonCustomAttributesFilters = [];
        $customAttributesFilters = [];
        $visibilityFilter = null;

        foreach ($this->filtersExtractor->extractFiltersFromQuery($request->getQuery()) as $filter) {
            if ($this->customAttributeFilterCheck->isCustom($filter)) {
                if ($filter->getField() === VisibilityFilter::VISIBILITY_FILTER_FIELD) {
                    $visibilityFilter = clone $filter;
                } else {
                    $customAttributesFilters[] = clone $filter;
                }
            } else {
                $nonCustomAttributesFilters[] = clone $filter;
            }
        }

        $data = [
            'select' => $this->resource->getConnection()->select(),
            'nonCustomAttributesFilters' => $nonCustomAttributesFilters,
            'customAttributesFilters' => $customAttributesFilters,
            'dimensions' => $request->getDimensions(),
            'isFullTextSearchRequired' => $this->fullTextSearchCheck->isRequiredForQuery($request->getQuery()),
            'isShowOutOfStockEnabled' => $this->isSetShowOutOfStockFlag(),
            'usedIndex' => $request->getIndex()
        ];

        if ($visibilityFilter !== null) {
            $data['visibilityFilter'] = $visibilityFilter;
        }

        return $this->selectContainerFactory->create($data);
    }

    /**
     * Checks if show_out_of_stock flag is enabled in current configuration
     *
     * @return bool
     */
    private function isSetShowOutOfStockFlag()
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );
    }
}
