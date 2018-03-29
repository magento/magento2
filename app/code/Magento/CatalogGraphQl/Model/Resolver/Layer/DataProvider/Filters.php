<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\FilterListFactory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogGraphQl\Model\Resolver\Layer\FilterableAttributesListFactory;

/**
 * Layered navigation filters data provider.
 *
 * @package Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider
 */
class Filters
{
    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var FilterableAttributesListFactory
     */
    private $filterableAttributesListFactory;

    /**
     * @var FilterListFactory
     */
    private $filterListFactory;

    public function __construct(
        Resolver $layerResolver,
        FilterableAttributesListFactory $filterableAttributesListFactory,
        FilterListFactory $filterListFactory
    ) {
        $this->layerResolver = $layerResolver;
        $this->filterableAttributesListFactory = $filterableAttributesListFactory;
        $this->filterListFactory = $filterListFactory;
    }

    /**
     * Get layered navigation filters data
     *
     * @param string $layerType
     * @return array
     */
    public function getData(string $layerType)
    {
        $filterableAttributesList = $this->filterableAttributesListFactory->create(
            $layerType
        );
        $filterList = $this->filterListFactory->create(
            [
                'filterableAttributes' => $filterableAttributesList
            ]
        );

        $filters = $filterList->getFilters($this->layerResolver->get());
        $filtersData = [];
        /** @var AbstractFilter $filter */
        foreach ($filters as $filter) {
            if ($filter->getItemsCount()) {
                $filterGroup = [
                    'name' => (string)$filter->getName(),
                    'filter_items_count' => $filter->getItemsCount(),
                    'request_var' => $filter->getRequestVar(),
                ];
                /** @var \Magento\Catalog\Model\Layer\Filter\Item $filterItem */
                foreach ($filter->getItems() as $filterItem) {
                    $filterGroup['filter_items'][] = [
                        'label' => (string)$filterItem->getLabel(),
                        'value_string' => $filterItem->getValueString(),
                        'items_count' => $filterItem->getCount(),
                    ];
                }
                $filtersData[] = $filterGroup;
            }
        }
        return $filtersData;
    }
}
