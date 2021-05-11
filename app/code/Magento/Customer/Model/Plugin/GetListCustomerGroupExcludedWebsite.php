<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\GroupExcludedWebsiteRepository;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Add excluded websites to customer groups as extension attributes while retrieving the list of all groups.
 */
class GetListCustomerGroupExcludedWebsite
{
    /**
     * @var \Magento\Customer\Api\Data\GroupExtensionInterfaceFactory
     */
    private $groupExtensionInterfaceFactory;

    /**
     * @var GroupExcludedWebsiteRepository
     */
    private $groupExcludedWebsiteRepository;

    /**
     * @param \Magento\Customer\Api\Data\GroupExtensionInterfaceFactory $groupExtensionInterfaceFactory
     * @param GroupExcludedWebsiteRepository $groupExcludedWebsiteRepository
     */
    public function __construct(
        \Magento\Customer\Api\Data\GroupExtensionInterfaceFactory $groupExtensionInterfaceFactory,
        GroupExcludedWebsiteRepository $groupExcludedWebsiteRepository
    ) {
        $this->groupExtensionInterfaceFactory = $groupExtensionInterfaceFactory;
        $this->groupExcludedWebsiteRepository = $groupExcludedWebsiteRepository;
    }

    /**
     * Add excluded websites to customer groups as extension attributes while retrieving the list of all groups.
     *
     * @param GroupRepositoryInterface $subject
     * @param GroupSearchResultsInterface $result
     * @param SearchCriteriaInterface $searchCriteria
     * @return GroupSearchResultsInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterGetList(
        GroupRepositoryInterface $subject,
        GroupSearchResultsInterface $result,
        SearchCriteriaInterface $searchCriteria
    ): GroupSearchResultsInterface {
        $customerGroups = $result->getItems();
        if (!empty($customerGroups)) {
            $allExcludedWebsites = $this->groupExcludedWebsiteRepository->getAllExcludedWebsites();
            if (!empty($allExcludedWebsites)) {
                foreach ($customerGroups as $customerGroup) {
                    $customerGroupId = (int)$customerGroup->getId();
                    if (array_key_exists($customerGroupId, $allExcludedWebsites)) {
                        $excludedWebsites = $allExcludedWebsites[$customerGroupId];
                        $customerGroupExtensionAttributes = $this->groupExtensionInterfaceFactory->create();
                        $customerGroupExtensionAttributes->setExcludeWebsiteIds($excludedWebsites);
                        $customerGroup->setExtensionAttributes($customerGroupExtensionAttributes);
                    }
                }
            }
        }

        return $result;
    }
}
