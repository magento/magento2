<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * Tier prices storage.
 * @api
 */
interface TierPriceStorageInterface
{
    /**
     * Return product prices.
     *
     * @param string[] $skus
     * @return \Magento\Catalog\Api\Data\TierPriceInterface[]
     */
    public function get(array $skus);

    /**
     * Add or update product prices.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface[] $prices
     * @return bool Will returned True if updated.
     */
    public function update(array $prices);

    /**
     * Remove existing tier prices and replace them with the new ones.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface[] $prices
     * @return bool Will returned True if replaced.
     */
    public function replace(array $prices);

    /**
     * Delete product tier prices.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface[] $prices
     * @return bool Will returned True if deleted.
     */
    public function delete(array $prices);
}
