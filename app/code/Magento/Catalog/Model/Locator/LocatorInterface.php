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
 */
interface LocatorInterface
{
    /**
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * @return StoreInterface
     */
    public function getStore();

    /**
     * @return array
     */
    public function getWebsiteIds();

    /**
     * @return string
     */
    public function getBaseCurrencyCode();
}
