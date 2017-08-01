<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Store cookie manager interface
 *
 * @api
 * @since 2.0.0
 */
interface StoreCookieManagerInterface
{
    /**
     * @return string
     * @since 2.0.0
     */
    public function getStoreCodeFromCookie();

    /**
     * @param StoreInterface $store
     * @return void
     * @since 2.0.0
     */
    public function setStoreCookie(StoreInterface $store);

    /**
     * @param StoreInterface $store
     * @return void
     * @since 2.0.0
     */
    public function deleteStoreCookie(StoreInterface $store);
}
