<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation;

use Magento\Framework\Api\Search\AggregationInterface;

/**
 * Build layer data from AggregationInterface
 * Return data in the following format:
 *
 * [
 *   [
 *     'name' => 'layer name',
 *     'filter_items_count' => 'filter items count',
 *     'request_var' => 'filter name in request',
 *     'filter_items' => [
 *         'label' => 'item name',
 *         'value_string' => 'item value, e.g. category ID',
 *         'items_count' => 'product count',
 *     ],
 *   ],
 *   ...
 * ];
 *
 * @api
 */
interface LayerBuilderInterface
{
    /**
     * Build layer data
     *
     * @param AggregationInterface $aggregation
     * @param int|null $storeId
     * @return array [[{layer data}], ...]
     */
    public function build(AggregationInterface $aggregation, ?int $storeId): array;
}
