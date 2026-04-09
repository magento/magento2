<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
