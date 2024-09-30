<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Formatter;

/**
 * Format Layered Navigation Items
 */
class LayerFormatter
{
    /**
     * Format layer data
     *
     * @param string $layerName
     * @param string $itemsCount
     * @param string $requestName
     * @param int $position
     * @return array
     */
    public function buildLayer($layerName, $itemsCount, $requestName, $position = null): array
    {
        return [
            'label' => __($layerName),
            'count' => $itemsCount,
            'attribute_code' => $requestName,
            'position' => isset($position) ? (int)$position : null
        ];
    }

    /**
     * Format layer item data
     *
     * @param string $label
     * @param string|int $value
     * @param string|int $count
     * @return array
     */
    public function buildItem($label, $value, $count): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'count' => $count,
        ];
    }
}
