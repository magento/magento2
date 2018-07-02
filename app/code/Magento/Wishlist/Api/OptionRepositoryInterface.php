<?php

namespace Magento\Wishlist\Api;

interface OptionRepositoryInterface
{
    /**
     * Get Wishlist by code
     * @param $sharingCode
     * @return \Magento\Wishlist\Api\Data\OptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($code): \Magento\Wishlist\Api\Data\OptionInterface;

    /**
     * Get Wishlist by id
     * @param int $id
     * @return \Magento\Wishlist\Api\Data\OptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id): \Magento\Wishlist\Api\Data\OptionInterface;


    /**
     * Get list of wishlists by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Wishlist\Api\Data\OptionSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria): \Magento\Wishlist\Api\Data\OptionSearchResultsInterface;

    /**
     * Save wishlist object
     *
     * @param \Magento\Wishlist\Api\Data\OptionInterface $wishlist
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\Wishlist\Api\Data\OptionInterface $wishlist): \Magento\Wishlist\Api\Data\OptionInterface;

    /**
     * Delete wishlist by id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id);

}
