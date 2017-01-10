<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleDefaultHydrator\Model\ResourceModel\Address;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\EntityManager\EntityManager;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var EntityManager
     */
    private $addressRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param AddressRepositoryInterface $addressRepositoryInterface
     * @param SearchCriteriaBuilder $SearchCriteriaBuilder
     */
    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->addressRepository = $addressRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param CustomerInterface $entity
     * @return CustomerInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('parent_id', $entity->getId())
            ->create();
        $addressesResult = $this->addressRepository->getList($searchCriteria);
        $entity->setAddresses($addressesResult->getItems());
        return $entity;
    }
}
