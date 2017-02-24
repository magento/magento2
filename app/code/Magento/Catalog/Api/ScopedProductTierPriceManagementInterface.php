<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 */
interface ScopedProductTierPriceManagementInterface
{
    /**
     * Create tier price for product
     *
     * @param string $sku
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function add($sku, \Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice);

    /**
     * Remove tier price from product
     *
     * @param string $sku
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function remove($sku, \Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice);

    /**
     * Get tier price of product
     *
     * @param string $sku
     * @param string $customerGroupId 'all' can be used to specify 'ALL GROUPS'
     * @return \Magento\Catalog\Api\Data\ProductTierPriceInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList($sku, $customerGroupId);
}
