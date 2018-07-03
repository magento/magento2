<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Website repository interface
 *
 * @api
 * @since 100.0.2
 */
interface WebsiteRepositoryInterface
{
    /**
     * Retrieve website by code
     *
     * @param string $code
     * @return \Magento\Store\Api\Data\WebsiteInterface
     * @throws NoSuchEntityException
     */
    public function get($code);

    /**
     * Retrieve website by id
     *
     * @param int $id
     * @return \Magento\Store\Api\Data\WebsiteInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Retrieve list of all websites
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getList();

    /**
     * Retrieve default website
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface
     * @throws \DomainException
     */
    public function getDefault();

    /**
     * Clear cached entities
     *
     * @return void
     */
    public function clean();
}
