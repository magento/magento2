<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * Special prices resource model.
 * @api
 * @since 102.0.0
 */
interface SpecialPriceInterface
{
    /**
     * Get product special prices by SKUs.
     *
     * @param string[] $skus Array containing SKUs
     *     $skus = [
     *         'sku value 1',
     *         'sku value 2'
     *     ];
     * @return [
     *      'entity_id' => (int) Entity identified or entity link field.
     *      'value' => (float) Special price value.
     *      'store_id' => (int) Store Id.
     *      'sku' => (string) Product SKU.
     *      'price_from' => (string) Special price from date value in UTC.
     *      'price_to' => (string) Special price to date value in UTC.
     * ]
     * @since 101.1.0
     * @since 102.0.0
     * @since 102.0.0
     */
    public function get(array $skus);

    /**
     * Update product special prices.
     *
     * @param array $prices
     *      $prices = [
     *          'entity_id' => (int) Entity identified or entity link field. Required.
     *          'attribute_id' => (int) Special price attribute Id. Required.
     *          'store_id' => (int) Store Id. Required.
     *          'value' => (float) Special price value. Required.
     *          'price_from' => (string) Special price from date value in Y-m-d H:i:s format in UTC. Optional.
     *          'price_to' => (string) Special price to date value in Y-m-d H:i:s format in UTC. Optional.
     *      ];
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException Thrown if error occurred during price save.
     * @since 102.0.0
     */
    public function update(array $prices);

    /**
     * Delete product special prices.
     *
     * @param array $prices
     *      $prices = [
     *          'entity_id' => (int) Entity identified or entity link field. Required.
     *          'attribute_id' => (int) Special price attribute Id. Required.
     *          'store_id' => (int) Store Id. Required.
     *          'value' => (float) Special price value. Required.
     *          'price_from' => (string) Special price from date value in Y-m-d H:i:s format in UTC. Optional.
     *          'price_to' => (string) Special price to date value in Y-m-d H:i:s format in UTC. Optional.
     *      ];
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException Thrown if error occurred during price delete.
     * @since 102.0.0
     */
    public function delete(array $prices);
}
