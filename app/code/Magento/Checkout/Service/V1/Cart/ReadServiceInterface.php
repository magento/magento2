<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Cart;

use Magento\Framework\Api\SearchCriteria;

/**
 * Cart read service interface.
 */
interface ReadServiceInterface
{
    /**
     * Enables an administrative user to return information for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart Cart object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getCart($cartId);

    /**
     * Returns information for the cart for a specified customer.
     *
     * @param int $customerId The customer ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart Cart object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer does not exist.
     */
    public function getCartForCustomer($customerId);

    /**
     * Enables administrative users to list carts that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Checkout\Service\V1\Data\CartSearchResults Cart search results object.
     */
    public function getCartList(SearchCriteria $searchCriteria);
}
