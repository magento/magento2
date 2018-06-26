<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Api;

interface WishlistManagementInterface
{
    /**
     * @param int $customerId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Wishlist\Api\Data\WishlistInterface
     */
    public function getWishlistByCustomerId($customerId);

    /**
     * @param int $customerId
     * @param string $sku
     * @throws \Magento\Framework\Exception\StateException
     * @return int
     */
    public function addWishlistItemByCustomerId($customerId, $sku);

}
