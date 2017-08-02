<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

/**
 * Store resolver interface
 *
 * @api
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCurrentStoreId();
}
