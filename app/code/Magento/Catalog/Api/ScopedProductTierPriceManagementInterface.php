<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 * @since 102.0.0
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
     * @since 102.0.0
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
     * @since 102.0.0
     */
    public function remove($sku, \Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice);

    /**
     * Get tier price of product
     *
     * @param string $sku
     * @param string $customerGroupId 'all' can be used to specify 'ALL GROUPS'
     * @return \Magento\Catalog\Api\Data\ProductTierPriceInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 102.0.0
     */
    public function getList($sku, $customerGroupId);
}
