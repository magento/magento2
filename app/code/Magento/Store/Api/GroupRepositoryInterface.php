<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     *
     * @return void
     */
    public function clean();
}
