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
 * @since 2.1.0
 */
interface LocatorInterface
{
    /**
     * @return ProductInterface
     * @since 2.1.0
     */
    public function getProduct();

    /**
     * @return StoreInterface
     * @since 2.1.0
     */
    public function getStore();

    /**
     * @return array
     * @since 2.1.0
     */
    public function getWebsiteIds();

    /**
     * @return string
     * @since 2.1.0
     */
    public function getBaseCurrencyCode();
}
