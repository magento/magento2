<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleDefaultHydrator\Model\ResourceModel\Address;

use \Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\EntityManager\EntityManager;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var EntityManager
     */
    private $addressRepositoryInterface;

    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface
     */
    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriBuilder
    ) {
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriBuilder;
    }

    /**
     * @param object $entity
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('parent_id', $entity->getId())
            ->create();
        $addressesResult = $this->addressRepositoryInterface->getList($searchCriteria);
        $entity->setAddresses($addressesResult->getItems());
        return $entity;
    }
}
