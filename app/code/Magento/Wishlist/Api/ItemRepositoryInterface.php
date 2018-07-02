<?php

namespace Magento\Wishlist\Api;

use Magento\Wishlist\Api\Data\ItemInterface;

/**
 * Interface ItemRepositoryInterface
 * @package Magento\Wishlist\Api
 */
interface ItemRepositoryInterface
{
    /**
     * Get wishlist item by id
     * @param int $id
     * @return \Magento\Wishlist\Api\Data\ItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id): \Magento\Wishlist\Api\Data\ItemInterface;

    /**
     * Get list of wishlist items by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Wishlist\Api\Data\ItemSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria): \Magento\Wishlist\Api\Data\ItemSearchResultsInterface;

    /**
     * Save wishlist item object
     *
     * @param \Magento\Wishlist\Api\Data\ItemInterface $item
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function save(\Magento\Wishlist\Api\Data\ItemInterface $item): \Magento\Wishlist\Api\Data\ItemInterface;

    /**
     * Delete item by id
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($id);

}
