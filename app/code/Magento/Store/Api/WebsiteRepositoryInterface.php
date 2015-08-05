<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Website repository interface
 *
 * @api
 */
interface WebsiteRepositoryInterface
{
    /**
     * Retrieve website by code
     *
     * @param string $code
     * @return Data\WebsiteInterface
     * @throws NoSuchEntityException
     */
    public function get($code);

    /**
     * Retrieve website by id
     *
     * @param int $id
     * @return Data\WebsiteInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Retrieve list of all websites
     *
     * @return Data\WebsiteInterface[]
     */
    public function getList();

    /**
     * Retrieve default website
     *
     * @return Data\WebsiteInterface
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
