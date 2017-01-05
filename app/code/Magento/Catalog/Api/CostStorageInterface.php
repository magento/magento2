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
     * Return product prices.
     *
     * @param string[] $skus
     * @return \Magento\Catalog\Api\Data\CostInterface[]
     */
    public function get(array $skus);

    /**
     * Add or update product cost.
     *
     * @param \Magento\Catalog\Api\Data\CostInterface[] $prices
     * @return bool Will returned True if updated.
     */
    public function update(array $prices);

    /**
     * Delete product cost.
     *
     * @param string[] $skus
     * @return bool Will returned True if deleted.
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(array $skus);
}
