<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Api;

interface CartRepositoryInterface
{
    /**
     * Enables an administrative user to return information for a specified cart.
     *
     * @param int $cartId
     * @return \Magento\Checkout\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @see \Magento\Checkout\Service\V1\Cart\ReadServiceInterface::getCart
     */
    public function get($cartId);

    /**
     * Enables administrative users to list carts that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magento\Checkout\Api\Data\CartSearchResultsInterface
     * @see \Magento\Checkout\Service\V1\Cart\ReadServiceInterface::getCartList
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);
}
