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
 */
interface StoreCookieManagerInterface
{
    /**
     * @return string
     */
    public function getStoreCodeFromCookie();

    /**
     * @param StoreInterface $store
     * @return void
     */
    public function setStoreCookie(StoreInterface $store);

    /**
     * @param StoreInterface $store
     * @return void
     */
    public function deleteStoreCookie(StoreInterface $store);
}
