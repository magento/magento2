<?php

namespace Magento\Wishlist\Api;

use Magento\Wishlist\Api\Data\WishlistInterface;
use Magento\Wishlist\Api\Data\WishlistSearchResultsInterface;

/**
 * Interface WishlistRepositoryInterface
 * @api
 * @package Magento\Wishlist\Api
 */
interface WishlistRepositoryInterface
{
    /**
     * Get Wishlist by id
     * @param int $id
     * @return \Magento\Wishlist\Api\Data\WishlistInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id): \Magento\Wishlist\Api\Data\WishlistInterface;

    /**
     * Get list of wishlists by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Wishlist\Api\Data\WishlistSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria): WishlistSearchResultsInterface;

    /**
     * Save wishlist object
     *
     * @param Data\WishlistInterface $wishlist
     * @return \Magento\Wishlist\Api\Data\WishlistInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\Wishlist\Api\Data\WishlistInterface $wishlist): WishlistInterface;

    /**
     * Delete wishlist by passing object
     *
     * @param \Magento\Wishlist\Api\Data\WishlistInterface $wishlist
     * @return bool
     */
    public function delete(\Magento\Wishlist\Api\Data\WishlistInterface $wishlist);

    /**
     * Delete wishlist by id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id);
}
