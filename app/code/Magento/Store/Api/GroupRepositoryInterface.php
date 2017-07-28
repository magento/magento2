<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Group repository interface
 *
 * @api
 * @since 2.0.0
 */
interface GroupRepositoryInterface
{
    /**
     * Retrieve group by id
     *
     * @param int $id
     * @return \Magento\Store\Api\Data\GroupInterface
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function get($id);

    /**
     * Retrieve list of all groups
     *
     * @return \Magento\Store\Api\Data\GroupInterface[]
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
