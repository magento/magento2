<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

/**
 * Store resolver interface
 *
 * @deprecated 101.0.0
 * @see \Magento\Store\Model\StoreManagerInterface
 */
interface StoreResolverInterface
{
    /**
     * Param name
     */
    const PARAM_NAME = '___store';

    /**
     * Retrieve current store id
     *
     * @return string
     */
    public function getCurrentStoreId();
}
