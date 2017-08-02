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
 * @since 2.0.0
 */
interface WebsiteRepositoryInterface
{
    /**
     * Retrieve website by code
     *
     * @param string $code
     * @return \Magento\Store\Api\Data\WebsiteInterface
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function get($code);

    /**
     * Retrieve website by id
     *
     * @param int $id
     * @return \Magento\Store\Api\Data\WebsiteInterface
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function getById($id);

    /**
     * Retrieve list of all websites
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     * @since 2.0.0
     */
    public function getList();

    /**
     * Retrieve default website
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface
     * @throws \DomainException
     * @since 2.0.0
     */
    public function getDefault();

    /**
     * Clear cached entities
     *
     * @return void
     * @since 2.0.0
     */
    public function clean();
}
