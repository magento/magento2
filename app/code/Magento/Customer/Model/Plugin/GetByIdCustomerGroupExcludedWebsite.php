<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\GroupExcludedWebsiteRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Add excluded websites to customer group as extension attributes while retrieving this group by id.
 */
class GetByIdCustomerGroupExcludedWebsite implements ResetAfterRequestInterface
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
     * In-request cache of excluded websites per customer group id
     *
     * @var array<int, array>
     */
    private $excludedWebsitesCache = [];

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
        if (!isset($this->excludedWebsitesCache[$id])) {
            $this->excludedWebsitesCache[$id] = $this->groupExcludedWebsiteRepository
                ->getCustomerGroupExcludedWebsites($id);
        }
        $excludedWebsites = $this->excludedWebsitesCache[$id];
        if (!empty($excludedWebsites)) {
            $customerGroupExtensionAttributes = $this->groupExtensionInterfaceFactory->create();
            $customerGroupExtensionAttributes->setExcludeWebsiteIds($excludedWebsites);
            $result->setExtensionAttributes($customerGroupExtensionAttributes);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->excludedWebsitesCache = [];
    }
}
