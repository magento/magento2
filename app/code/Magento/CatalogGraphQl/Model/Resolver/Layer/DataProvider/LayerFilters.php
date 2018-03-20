<?php

namespace Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\FilterListFactory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogGraphQl\Model\Resolver\Layer\FilterableAttributesListFactory;
use Magento\Framework\Registry;

class LayerFilters
{

    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var FilterListFactory
     */
    private $filterListFactory;

    /**
     * @var FilterableAttributesListFactory
     */
    private $filterableAttributesListFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * LayerFilters constructor.
     * @param Resolver $layerResolver
     * @param FilterListFactory $filterListFactory
     * @param FilterableAttributesListFactory $filterableAttributesListFactory
     */
    public function __construct(
        Resolver $layerResolver,
        FilterListFactory $filterListFactory,
        FilterableAttributesListFactory $filterableAttributesListFactory,
        Registry $registry
    ) {
        $this->layerResolver = $layerResolver;
        $this->filterListFactory = $filterListFactory;
        $this->filterableAttributesListFactory = $filterableAttributesListFactory;
        $this->registry = $registry;
    }

    /**
     * Get filters.
     *
     * @param string $type
     * @return array
     */
    public function getFilters($type)
    {
        $filterableAttributesList = $this->filterableAttributesListFactory->create(
            Resolver::CATALOG_LAYER_SEARCH
        );
        /** @var \Magento\Catalog\Model\Layer $layer */
        $layer = $this->layerResolver->get();
        $filterList = $this->filterListFactory->create(
            [
                'filterableAttributes' => $filterableAttributesList
            ]
        );
        $filters = $filterList->getFilters($layer);
        $filtersArray = [];
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
                $filtersArray[] = $filterGroup;
            }
        }
        return $filtersArray;
    }
}
