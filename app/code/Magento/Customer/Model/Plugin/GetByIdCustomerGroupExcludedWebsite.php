<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Cache\GroupExcludedWebsiteCache;
use Magento\Customer\Model\ResourceModel\GroupExcludedWebsiteRepository;
use Magento\Framework\Exception\LocalizedException;

/**
 * Add excluded websites to customer group as extension attributes while retrieving this group by id.
 *
 * Uses shared GroupExcludedWebsiteCache so cache is properly invalidated when excluded websites
 * are modified by SaveCustomerGroupExcludedWebsite or DeleteCustomerGroupExcludedWebsite.
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
     * @var GroupExcludedWebsiteCache
     */
    private $groupExcludedWebsiteCache;

    /**
     * @param \Magento\Customer\Api\Data\GroupExtensionInterfaceFactory $groupExtensionInterfaceFactory
     * @param GroupExcludedWebsiteRepository $groupExcludedWebsiteRepository
     * @param GroupExcludedWebsiteCache $groupExcludedWebsiteCache
     */
    public function __construct(
        \Magento\Customer\Api\Data\GroupExtensionInterfaceFactory $groupExtensionInterfaceFactory,
        GroupExcludedWebsiteRepository $groupExcludedWebsiteRepository,
        GroupExcludedWebsiteCache $groupExcludedWebsiteCache
    ) {
        $this->groupExtensionInterfaceFactory = $groupExtensionInterfaceFactory;
        $this->groupExcludedWebsiteRepository = $groupExcludedWebsiteRepository;
        $this->groupExcludedWebsiteCache = $groupExcludedWebsiteCache;
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
        if ($this->groupExcludedWebsiteCache->isCached($id)) {
            $excludedWebsites = $this->groupExcludedWebsiteCache->getFromCache($id);
        } else {
            $excludedWebsites = $this->groupExcludedWebsiteRepository->getCustomerGroupExcludedWebsites($id);
            // Resource model populates cache when loading from DB
        }

        if (!empty($excludedWebsites)) {
            $customerGroupExtensionAttributes = $this->groupExtensionInterfaceFactory->create();
            $customerGroupExtensionAttributes->setExcludeWebsiteIds($excludedWebsites);
            $result->setExtensionAttributes($customerGroupExtensionAttributes);
        }

        return $result;
    }
}
