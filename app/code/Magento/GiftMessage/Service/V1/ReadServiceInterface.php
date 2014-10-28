<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
