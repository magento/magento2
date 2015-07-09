<?php
/**
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Store\Api;

use Magento\Framework\Exception\NoSuchEntityException;

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
     * @return Data\StoreInterface
     * @throws NoSuchEntityException
     */
    public function get($code);

    /**
     * Retrieve store by id
     *
     * @param int $id
     * @return Data\StoreInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Retrieve list of all stores
     *
     * @return Data\StoreInterface[]
     */
    public function getList();

    /**
     * Clear cached entities
     *
     * @return void
     */
    public function clean();
}
