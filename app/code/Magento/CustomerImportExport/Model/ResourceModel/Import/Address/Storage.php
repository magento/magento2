<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\ResourceModel\Import\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Storage to check existing addresses.
 */
class Storage
{
    /**
     * IDs of addresses grouped by customer IDs.
     *
     * @var string[][]
     */
    private $addresses = [];

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->addressRepository = $addressRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Record existing address.
     *
     * @param string $customerId
     * @param string $addressId
     *
     * @return void
     */
    private function addRecord($customerId, $addressId)
    {
        if (!$customerId || !$addressId) {
            return;
        }
        $customerId = (string)$customerId;
        $addressId = (string)$addressId;
        if (!array_key_exists($customerId, $this->addresses)) {
            $this->addresses[$customerId] = [];
        }

        if (!in_array($addressId, $this->addresses[$customerId], true)) {
            $this->addresses[$customerId][] = $addressId;
        }
    }

    /**
     * Check if given address exists for given customer.
     *
     * @param string $addressId
     * @param string $forCustomerId
     * @return bool
     */
    public function doesExist($addressId, $forCustomerId)
    {
        return array_key_exists($forCustomerId, $this->addresses)
            && in_array(
                (string)$addressId,
                $this->addresses[$forCustomerId],
                true
            );
    }

    /**
     * Pre-load addresses for given customers.
     *
     * @param string[] $forCustomersIds
     * @return void
     */
    public function prepareAddresses(array $forCustomersIds)
    {
        if (!$forCustomersIds) {
            return;
        }

        $filters = [];
        foreach ($forCustomersIds as $customerId) {
            if (!array_key_exists((string)$customerId, $this->addresses)) {
                $filters[] = $this->filterBuilder
                    ->setField('parent_id')
                    ->setValue($customerId)
                    ->setConditionType('eq')
                    ->create();
            }
        }
        $this->searchCriteriaBuilder->addFilters($filters);

        //Adding addresses that we found.
        $found = $this->addressRepository->getList(
            $this->searchCriteriaBuilder->create()
        );
        foreach ($found->getItems() as $address) {
            $this->addRecord($address->getCustomerId(), $address->getId());
        }
    }
}
