<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GiftMessage\Api;

interface GiftMessageRepositoryInterface
{
    /**
     * Returns the gift message for a specified order.
     *
     * @param int $cartId The shopping cart ID.
     * @return \Magento\GiftMessage\Service\V1\Data\Message Gift message.
     * @see \Magento\GiftMessage\Service\V1\ReadServiceInterface::get
     */
    public function get($cartId);
}
