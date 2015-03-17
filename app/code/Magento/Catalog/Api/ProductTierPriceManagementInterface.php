<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

interface ProductTierPriceManagementInterface
{
    /**
     * Create tier price for product
     *
     * @param string $sku
     * @param string $customerGroupId
     * @param float $price
     * @param float $qty
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function add($sku, $customerGroupId, $price, $qty);

    /**
     * Remove tire price from product
     *
     * @param string $sku
     * @param string $customerGroupId
     * @param float $qty
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function remove($sku, $customerGroupId, $qty);

    /**
     * Get tire price of product
     *
     * @param string $sku
     * @param string $customerGroupId
     * @return \Magento\Catalog\Api\Data\ProductTierPriceInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList($sku, $customerGroupId);
}
