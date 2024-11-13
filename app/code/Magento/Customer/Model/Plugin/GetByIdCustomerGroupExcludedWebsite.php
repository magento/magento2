<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\GroupExcludedWebsiteRepository;
use Magento\Framework\Exception\LocalizedException;

/**
 * Add excluded websites to customer group as extension attributes while retrieving this group by id.
 */
class GetByIdCustomerGroupExcludedWebsite
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
     * Add excluded websites as extension attributes while getting customer group by id.
     *
     * @param GroupRepositoryInterface $subject
     * @param GroupInterface $result
     * @param int $id
     * @return GroupInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterGetById(
        GroupRepositoryInterface $subject,
        GroupInterface $result,
        int $id
    ): GroupInterface {
        $excludedWebsites = $this->groupExcludedWebsiteRepository->getCustomerGroupExcludedWebsites($id);
        if (!empty($excludedWebsites)) {
            $customerGroupExtensionAttributes = $this->groupExtensionInterfaceFactory->create();
            $customerGroupExtensionAttributes->setExcludeWebsiteIds($excludedWebsites);
            $result->setExtensionAttributes($customerGroupExtensionAttributes);
        }

        return $result;
    }
}
