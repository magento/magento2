<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\Store\Api\Data\GroupInterface;

/**
 * Class GetDefaultStoreGroup
 * Get default (first) store group;
 */
class GetDefaultStoreGroup
{
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var \Magento\Store\Api\Data\GroupInterfaceFactory
     */
    private $groupFactory;

    /**
     *
     * @param \Magento\Store\Api\Data\WebsiteInterfaceFactory $websiteFactory
     * @param \Magento\Store\Api\Data\GroupInterfaceFactory $groupFactory
     */
    public function __construct(
        \Magento\Store\Api\Data\WebsiteInterfaceFactory $websiteFactory,
        \Magento\Store\Api\Data\GroupInterfaceFactory $groupFactory
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->groupFactory = $groupFactory;
    }

    /**
     *  Get default group
     *
     * @return GroupInterface
     */
    public function execute() : GroupInterface
    {
        $groups = $this->getAllStoreGroups();

        return current($groups);
    }


    /**
     * Retrieve list of store groups
     *
     * @return array
     */
    private function getAllStoreGroups() : array
    {
        $websites = $this->websiteFactory->create()->getCollection();
        $allgroups = $this->groupFactory->create()->getCollection();
        $groups = [];
        foreach ($websites as $website) {
            foreach ($allgroups as $group) {
                if ($group->getWebsiteId() == $website->getId()) {
                    $groups[$group->getWebsiteId() . '-' . $group->getId()] = $group;
                }
            }
        }

        return $groups;
    }
}
