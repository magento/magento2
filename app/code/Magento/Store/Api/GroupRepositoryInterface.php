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
 * Group repository interface
 *
 * @api
 */
interface GroupRepositoryInterface
{
    /**
     * Retrieve group by id
     *
     * @param int $id
     * @return Data\GroupInterface
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * Retrieve list of all groups
     *
     * @return Data\GroupInterface[]
     */
    public function getList();

    /**
     * Clear cached entities
     */
    public function clean();
}
