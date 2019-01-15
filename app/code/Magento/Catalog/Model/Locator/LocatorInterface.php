<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
