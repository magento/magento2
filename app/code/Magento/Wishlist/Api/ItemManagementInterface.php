<?php

namespace Magento\Wishlist\Api;

/**
 * Interface ItemManagementInterface
 * @package Magento\Wishlist\Api
 */
interface ItemManagementInterface
{
    /**
     * @return mixed
     */
    public function loadByProductWishlist();
}
