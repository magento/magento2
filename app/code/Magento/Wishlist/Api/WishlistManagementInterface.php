<?php

namespace Magento\Wishlist\Api;

interface WishlistManagementInterface
{
    /**
     * @param int $customerId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Wishlist\Api\Data\WishlistInterface
     */
    public function getWishlistForCustomer($customerId);

    /**
     * @param int $customerId
     * @param int $productId
     * @throws \Magento\Framework\Exception\StateException
     * @return int
     */
    public function addWishlistForCustomer($customerId, $productId);

}
