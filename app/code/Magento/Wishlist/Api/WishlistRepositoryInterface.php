<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Api;

use Magento\Wishlist\Api\Data\WishlistInterface;

/**
 * Interface WishlistRepositoryInterface
 * @api
 * @package Magento\Wishlist\Api
 */
interface WishlistRepositoryInterface
{
    /**
     * Get Wishlist by sharing code
     * @param $sharingCode
     * @return \Magento\Wishlist\Api\Data\WishlistInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($sharingCode): \Magento\Wishlist\Api\Data\WishlistInterface;

    /**
     * Get Wishlist by id
     * @param int $id
     * @return \Magento\Wishlist\Api\Data\WishlistInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id): \Magento\Wishlist\Api\Data\WishlistInterface;

    /**
     * Get Wishlist by customer
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     */
    public function getByCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer): \Magento\Wishlist\Api\Data\WishlistInterface;

    /**
     * Get list of wishlists by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Wishlist\Api\Data\WishlistSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria): \Magento\Wishlist\Api\Data\WishlistSearchResultsInterface;

    /**
     * Save wishlist object
     *
     * @param \Magento\Wishlist\Api\Data\WishlistInterface $wishlist
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\Wishlist\Api\Data\WishlistInterface $wishlist): \Magento\Wishlist\Api\Data\WishlistInterface;

    /**
     * Delete wishlist by id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id);
}
