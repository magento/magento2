<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Locator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Interface LocatorInterface
 *
 * @api
 * @since 101.0.0
 */
interface LocatorInterface
{
    /**
     * @return ProductInterface
     * @since 101.0.0
     */
    public function getProduct();

    /**
     * @return StoreInterface
     * @since 101.0.0
     */
    public function getStore();

    /**
     * @return array
     * @since 101.0.0
     */
    public function getWebsiteIds();

    /**
     * @return string
     * @since 101.0.0
     */
    public function getBaseCurrencyCode();
}
