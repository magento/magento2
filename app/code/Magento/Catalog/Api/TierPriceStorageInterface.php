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
     * Return product prices. In case of at least one of skus is not found exception will be thrown.
     *
     * @param string[] $skus
     * @return \Magento\Catalog\Api\Data\TierPriceInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(array $skus);

    /**
     * Add or update product prices.
     * If any items will have invalid price, price type, website id, sku, customer group or quantity, they will be
     * marked as failed and excluded from update list and \Magento\Catalog\Api\Data\PriceUpdateResultInterface[]
     * with problem description will be returned.
     * If there were no failed items during update empty array will be returned.
     * If error occurred during the update exception will be thrown.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface[] $prices
     * @return \Magento\Catalog\Api\Data\PriceUpdateResultInterface[]
     */
    public function update(array $prices);

    /**
     * Remove existing tier prices and replace them with the new ones.
     * If any items will have invalid price, price type, website id, sku, customer group or quantity, they will be
     * marked as failed and excluded from replace list and \Magento\Catalog\Api\Data\PriceUpdateResultInterface[]
     * with problem description will be returned.
     * If there were no failed items during update empty array will be returned.
     * If error occurred during the update exception will be thrown.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface[] $prices
     * @return \Magento\Catalog\Api\Data\PriceUpdateResultInterface[]
     */
    public function replace(array $prices);

    /**
     * Delete product tier prices.
     * If any items will have invalid price, price type, website id, sku, customer group or quantity, they will be
     * marked as failed and excluded from delete list and \Magento\Catalog\Api\Data\PriceUpdateResultInterface[]
     * with problem description will be returned.
     * If there were no failed items during update empty array will be returned.
     * If error occurred during the update exception will be thrown.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceInterface[] $prices
     * @return \Magento\Catalog\Api\Data\PriceUpdateResultInterface[]
     */
    public function delete(array $prices);
}
