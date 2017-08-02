<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 * @deprecated 2.2.0 use ScopedProductTierPriceManagementInterface instead
 * @since 2.0.0
 */
interface ProductTierPriceManagementInterface
{
    /**
     * Create tier price for product
     *
     * @param string $sku
     * @param string $customerGroupId 'all' can be used to specify 'ALL GROUPS'
     * @param float $price
     * @param float $qty
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @since 2.0.0
     */
    public function add($sku, $customerGroupId, $price, $qty);

    /**
     * Remove tier price from product
     *
     * @param string $sku
     * @param string $customerGroupId 'all' can be used to specify 'ALL GROUPS'
     * @param float $qty
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @since 2.0.0
     */
    public function remove($sku, $customerGroupId, $qty);

    /**
     * Get tier price of product
     *
     * @param string $sku
     * @param string $customerGroupId 'all' can be used to specify 'ALL GROUPS'
     * @return \Magento\Catalog\Api\Data\ProductTierPriceInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.0.0
     */
    public function getList($sku, $customerGroupId);
}
