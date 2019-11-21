<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SwatchesGraphQl\Plugin\Filters;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\Filters;
use Magento\CatalogGraphQl\Model\Resolver\Layer\FiltersProvider;

/**
 * Plugin to add swatch data to filters data from filters data provider.
 */
class DataProviderPlugin
{
    /**
     * @var FiltersProvider
     */
    private $filtersProvider;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    private $swatchHelper;

    /**
     * @var \Magento\Swatches\Block\LayeredNavigation\RenderLayered
     */
    private $renderLayered;

    /**
     * Filters constructor.
     *
     * @param FiltersProvider $filtersProvider
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param \Magento\Swatches\Block\LayeredNavigation\RenderLayered $renderLayered
     */
    public function __construct(
        FiltersProvider $filtersProvider,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Swatches\Block\LayeredNavigation\RenderLayered $renderLayered
    ) {
        $this->filtersProvider = $filtersProvider;
        $this->swatchHelper = $swatchHelper;
        $this->renderLayered = $renderLayered;
    }

    /**
     * Using around as layout type has to be passed.
     *
     * @param Filters $subject
     * @param \Closure $proceed
     * @param string $layerType
     * @param array|null $attributesToFilter
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    public function aroundGetData(
        Filters $subject,
        \Closure $proceed,
        string $layerType,
        $attributesToFilter = null
    ) : array {
        $swatchFilters = [];
        /** @var AbstractFilter $filter */
        foreach ($this->filtersProvider->getFilters($layerType) as $filter) {
            if ($filter->hasAttributeModel()) {
                if ($this->swatchHelper->isSwatchAttribute($filter->getAttributeModel())) {
                    $swatchFilters[] = $filter;
                }
            }
        }

        $filtersData = $proceed($layerType, $attributesToFilter);

        foreach ($filtersData as $groupKey => $filterGroup) {
            /** @var AbstractFilter $swatchFilter */
            foreach ($swatchFilters as $swatchFilter) {
                if ($filterGroup['request_var'] === $swatchFilter->getRequestVar()) {
                    $swatchData = $this->renderLayered->setSwatchFilter($swatchFilter)->getSwatchData();
                    foreach ($filterGroup['filter_items'] as $itemKey => $filterItem) {
                        foreach ((array)$swatchData['swatches'] as $swatchKey => $swatchDataItem) {
                            if ($filterItem['value_string'] == $swatchKey) {
                                $filtersData[$groupKey]['filter_items'][$itemKey]['swatch_data'] = [
                                    'type' => $swatchDataItem['type'],
                                    'value' => $swatchDataItem['value']
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $filtersData;
    }
    //phpcs:enable
}
