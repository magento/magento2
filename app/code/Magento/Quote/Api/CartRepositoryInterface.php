<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

/**
 * Interface CartRepositoryInterface
 * @api
 */
interface CartRepositoryInterface
{
    /**
     * Enables an administrative user to return information for a specified cart.
     *
     * @param int $cartId
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($cartId);

    /**
     * Enables administrative users to list carts that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magento\Quote\Api\Data\CartSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * Get quote by customer Id
     *
     * @param int $customerId
     * @param int[] $sharedStoreIds
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getForCustomer($customerId, array $sharedStoreIds = []);

    /**
     * Get active quote by id
     *
     * @param int $cartId
     * @param int[] $sharedStoreIds
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActive($cartId, array $sharedStoreIds = []);

    /**
     * Get active quote by customer Id
     *
     * @param int $customerId
     * @param int[] $sharedStoreIds
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActiveForCustomer($customerId, array $sharedStoreIds = []);

    /**
     * Save quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    public function save(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * Delete quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    public function delete(\Magento\Quote\Api\Data\CartInterface $quote);
}
