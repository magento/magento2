<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

/**
 * Customer address CRUD interface.
 * @api
 * @since 100.0.2
 */
interface AddressRepositoryInterface
{
    /**
     * Save customer address.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\Customer\Api\Data\AddressInterface $address);

    /**
     * Retrieve customer address.
     *
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($addressId);

    /**
     * Retrieve customers addresses matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Customer\Api\Data\AddressSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete customer address.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magento\Customer\Api\Data\AddressInterface $address);

    /**
     * Delete customer address by ID.
     *
     * @param int $addressId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($addressId);
}
