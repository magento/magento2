<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * Base prices storage.
 * @api
 */
interface BasePriceStorageInterface
{
    /**
     * Return product prices.
     *
     * @param string[] $skus
     * @return \Magento\Catalog\Api\Data\BasePriceInterface[]
     */
    public function get(array $skus);

    /**
     * Add or update product prices.
     *
     * @param \Magento\Catalog\Api\Data\BasePriceInterface[] $prices
     * @return bool Will returned True if updated.
     */
    public function update(array $prices);
}
