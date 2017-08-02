<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

/**
 * Interface \Magento\Store\Model\StoreResolver\ReaderInterface
 *
 * @since 2.0.0
 */
interface ReaderInterface
{
    /**
     * Retrieve list of stores available for scope
     *
     * @param string $scopeCode
     * @return array
     * @since 2.0.0
     */
    public function getAllowedStoreIds($scopeCode);

    /**
     * Retrieve default store id
     *
     * @param string $scopeCode
     * @return int
     * @since 2.0.0
     */
    public function getDefaultStoreId($scopeCode);
}
