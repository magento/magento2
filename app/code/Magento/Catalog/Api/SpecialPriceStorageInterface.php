<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * Special price storage presents efficient price API and is used to retrieve, update or delete special prices.
 * @api
 */
interface SpecialPriceStorageInterface
{
    /**
     * Return product's special price. In case of at least one of skus is not found exception will be thrown.
     *
     * @param string[] $skus
     * @return \Magento\Catalog\Api\Data\SpecialPriceInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(array $skus);

    /**
     * Add or update product's special price.
     * If any items will have invalid price, store id, sku or dates, they will be marked as failed and excluded from
     * update list and \Magento\Catalog\Api\Data\PriceUpdateResultInterface[] with problem description will be returned.
     * If there were no failed items during update empty array will be returned.
     * If error occurred during the update exception will be thrown.
     *
     * @param \Magento\Catalog\Api\Data\SpecialPriceInterface[] $prices
     * @return \Magento\Catalog\Api\Data\PriceUpdateResultInterface[]
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function update(array $prices);

    /**
     * Delete product's special price.
     * If any items will have invalid price, store id, sku or dates, they will be marked as failed and excluded from
     * delete list and \Magento\Catalog\Api\Data\PriceUpdateResultInterface[] with problem description will be returned.
     * If there were no failed items during update empty array will be returned.
     * If error occurred during the delete exception will be thrown.
     *
     * @param \Magento\Catalog\Api\Data\SpecialPriceInterface[] $prices
     * @return \Magento\Catalog\Api\Data\PriceUpdateResultInterface[]
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(array $prices);
}
