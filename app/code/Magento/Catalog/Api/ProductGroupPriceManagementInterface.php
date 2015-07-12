<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * @api
 */
interface ProductGroupPriceManagementInterface
{
    /**
     * Set group price for product
     *
     * @param string $sku
     * @param int $customerGroupId
     * @param float $price
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function add($sku, $customerGroupId, $price);

    /**
     * Remove group price from product
     *
     * @param string $sku
     * @param int $customerGroupId
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function remove($sku, $customerGroupId);

    /**
     * Retrieve list of product prices
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductGroupPriceInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList($sku);
}
