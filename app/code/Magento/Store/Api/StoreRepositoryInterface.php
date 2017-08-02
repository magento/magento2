<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreIsInactiveException;

/**
 * Store repository interface
 *
 * @api
 * @since 2.0.0
 */
interface StoreRepositoryInterface
{
    /**
     * Retrieve store by code
     *
     * @param string $code
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function get($code);

    /**
     * Retrieve active store by code
     *
     * @param string $code
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws NoSuchEntityException
     * @throws StoreIsInactiveException
     * @since 2.0.0
     */
    public function getActiveStoreByCode($code);

    /**
     * Retrieve active store by id
     *
     * @param int $id
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws NoSuchEntityException
     * @throws StoreIsInactiveException
     * @since 2.0.0
     */
    public function getActiveStoreById($id);

    /**
     * Retrieve store by id
     *
     * @param int $id
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function getById($id);

    /**
     * Retrieve list of all stores
     *
     * @return \Magento\Store\Api\Data\StoreInterface[]
     * @since 2.0.0
     */
    public function getList();

    /**
     * Clear cached entities
     *
     * @return void
     * @since 2.0.0
     */
    public function clean();
}
