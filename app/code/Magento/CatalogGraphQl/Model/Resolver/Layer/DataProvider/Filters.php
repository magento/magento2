<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\CatalogGraphQl\Model\Resolver\Layer\FiltersProvider;
use Magento\Catalog\Model\Layer\Filter\Item;

/**
 * Layered navigation filters data provider.
 */
class Filters
{
    /**
     * @var FiltersProvider
     */
    private $filtersProvider;

    /**
     * @var array
     */
    private $mappings;

    /**
     * Filters constructor.
     * @param FiltersProvider $filtersProvider
     */
    public function __construct(
        FiltersProvider $filtersProvider
    ) {
        $this->filtersProvider = $filtersProvider;
        $this->mappings = [
            'Category' => 'category'
        ];
    }

    /**
     * Get layered navigation filters data
     *
     * @param string $layerType
     * @param array|null $attributesToFilter
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData(string $layerType, array $attributesToFilter = null) : array
    {
        $filtersData = [];
        /** @var AbstractFilter $filter */
        foreach ($this->filtersProvider->getFilters($layerType) as $filter) {
            if ($this->isNeedToAddFilter($filter, $attributesToFilter)) {
                $filterGroup = [
                    'name' => (string)$filter->getName(),
                    'filter_items_count' => $filter->getItemsCount(),
                    'request_var' => $filter->getRequestVar(),
                ];
                /** @var Item $filterItem */
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

    /**
     * Check for adding filter to the list
     *
     * @param AbstractFilter $filter
     * @param array $attributesToFilter
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isNeedToAddFilter(AbstractFilter $filter, array $attributesToFilter): bool
    {
        if ($attributesToFilter === null) {
            $result = (bool)$filter->getItemsCount();
        } else {
            if ($filter->hasAttributeModel()) {
                $filterAttribute = $filter->getAttributeModel();
                $result = in_array($filterAttribute->getAttributeCode(), $attributesToFilter);
            } else {
                $name = (string)$filter->getName();
                if (array_key_exists($name, $this->mappings)) {
                    $result = in_array($this->mappings[$name], $attributesToFilter);
                } else {
                    $result = true;
                }
            }
        }
        return $result;
    }
}
