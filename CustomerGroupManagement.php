<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\GroupRepositoryInterface;

/**
 * Helper class for operations with customer group records.
 */
class CustomerGroupManagement
{
    /**
     * @var string[]
     */
    protected $consumers = [];
    
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Delete customer groups by codes.
     * 
     * @param string[] $customerGroupCodes
     */
    public function deleteCustomerGroups($customerGroupCodes)
    {
        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->objectManager->get(GroupRepositoryInterface::class);
        foreach ($this->loadCustomerGroups($customerGroupCodes)->getItems() as $customerGroup) {
            $groupRepository->delete($customerGroup);
        }
    }
    
    /**
     * Load customer groups by codes.
     *
     * @param string[] $customerGroupCodes
     * @return \Magento\Customer\Api\Data\GroupSearchResultsInterface
     */
    public function loadCustomerGroups($customerGroupCodes)
    {
        /** @var $searchCriteriaBuilder  \Magento\Framework\Api\SearchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
        /** @var $filterBuilder  \Magento\Framework\Api\FilterBuilder */
        $filterBuilder = $this->objectManager->create('Magento\Framework\Api\FilterBuilder');
        $searchCriteriaBuilder->addFilters(
            [
                $filterBuilder->setField('code')
                    ->setValue($customerGroupCodes)
                    ->setConditionType('in')
                    ->create()
            ]
        );
        $searchCriteria = $searchCriteriaBuilder->create();
        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->objectManager->get(GroupRepositoryInterface::class);
        $customerGroups = $groupRepository->getList($searchCriteria);
        return $customerGroups;
    }
}
