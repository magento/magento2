<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Service\V1;

/**
 * Quote shipping method read service interface.
 */
interface ReadServiceInterface
{
    /**
     * Returns the gift message for a specified order.
     *
     * @param int $cartId The shopping cart ID.
     * @return \Magento\GiftMessage\Service\V1\Data\Message Gift message.
     */
    public function get($cartId);

    /**
     * Returns the gift message for a specified item in a specified shopping cart.
     *
     * @param int $cartId The shopping cart ID.
     * @param int $itemId The item ID.
     * @return \Magento\GiftMessage\Service\V1\Data\Message Gift message.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item does not exist in the cart.
     */
    public function getItemMessage($cartId, $itemId);
}
