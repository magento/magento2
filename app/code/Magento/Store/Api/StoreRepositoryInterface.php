<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreIsInactiveException;

/**
 * Store repository interface
 *
 * @api
 */
interface StoreRepositoryInterface
{
    /**
     * Retrieve store by code
     *
     * @param string $code
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws NoSuchEntityException
     */
    public function get($code);

    /**
     * Retrieve active store by code
     *
     * @param string $code
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws NoSuchEntityException
     * @throws StoreIsInactiveException
     */
    public function getActiveStoreByCode($code);

    /**
     * Retrieve active store by id
     *
     * @param int $id
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws NoSuchEntityException
     * @throws StoreIsInactiveException
     */
    public function getActiveStoreById($id);

    /**
     * Retrieve store by id
     *
     * @param int $id
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Retrieve list of all stores
     *
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function getList();

    /**
     * Clear cached entities
     *
     * @return void
     */
    public function clean();
}
