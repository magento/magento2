<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * Product cost storage.
 * @api
 */
interface CostStorageInterface
{
    /**
     * Return product prices. In case of at least one of skus is not found exception will be thrown.
     *
     * @param string[] $skus
     * @return \Magento\Catalog\Api\Data\CostInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(array $skus);

    /**
     * Add or update product cost.
     * Input item should correspond to \Magento\Catalog\Api\Data\CostInterface.
     * If any items will have invalid cost, store id or sku, they will be marked as failed and excluded from
     * update list and \Magento\Catalog\Api\Data\PriceUpdateResultInterface[] with problem description will be returned.
     * If there were no failed items during update empty array will be returned.
     * If error occurred during the update exception will be thrown.
     *
     * @param \Magento\Catalog\Api\Data\CostInterface[] $prices
     * @return \Magento\Catalog\Api\Data\PriceUpdateResultInterface[]
     */
    public function update(array $prices);

    /**
     * Delete product cost. In case of at least one of skus is not found exception will be thrown.
     * If error occurred during the delete exception will be thrown.
     *
     * @param string[] $skus
     * @return bool Will return True if deleted.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(array $skus);
}
