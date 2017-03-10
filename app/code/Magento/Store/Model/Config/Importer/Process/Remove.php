<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Process;

use Magento\Framework\Registry;
use Magento\Store\Model\Config\Importer\DataDifferenceFactory;
use Magento\Store\Model\WebsiteRepository;
use Magento\Store\Model\GroupRepository;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;

class Remove
{
    /**
     * @var DataDifferenceFactory
     */
    private $dataDifferenceFactory;

    /**
     * @var WebsiteRepository
     */
    private $websiteRepository;

    /**
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var Website
     */
    private $websiteResource;

    /**
     * @var Store
     */
    private $storeResource;

    /**
     * @var Group
     */
    private $groupResource;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry $registry
     * @param DataDifferenceFactory $dataDifferenceFactory
     * @param WebsiteRepository $websiteRepository
     * @param StoreRepository $storeRepository
     * @param GroupRepository $groupRepository
     * @param Website $websiteResource
     * @param Store $storeResource
     * @param Group $groupResource
     */
    public function __construct(
        Registry $registry,
        DataDifferenceFactory $dataDifferenceFactory,
        WebsiteRepository $websiteRepository,
        StoreRepository $storeRepository,
        GroupRepository $groupRepository,
        Website $websiteResource,
        Store $storeResource,
        Group $groupResource
    ) {
        $this->registry = $registry;
        $this->dataDifferenceFactory = $dataDifferenceFactory;
        $this->websiteRepository = $websiteRepository;
        $this->storeRepository = $storeRepository;
        $this->groupRepository = $groupRepository;
        $this->websiteResource = $websiteResource;
        $this->storeResource = $storeResource;
        $this->groupResource = $groupResource;
    }

    /**
     * @param $data
     * @return boolean
     */
    public function run($data)
    {
        $this->registry->register('isSecureArea', true);

        // Remove records that not exists in import configuration
        // First we need to remove groups and stores and then websites
        foreach (['groups', 'stores', 'websites'] as $scope) {
            $dataDifference = $this->dataDifferenceFactory->create($scope);
            $itemsToDelete = $dataDifference->getItemsToDelete($data[$scope]);

            if (empty($itemsToDelete)) {
                continue;
            }

            try {
                if ($scope == 'websites') {
                    $this->deleteWebsites($itemsToDelete);
                } else if ($scope == 'stores') {
                    $this->deleteStores($itemsToDelete);
                } else if ($scope == 'groups') {
                    $this->deleteGroups($itemsToDelete);
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $itemsToDelete
     * @return void
     */
    private function deleteWebsites(array $itemsToDelete)
    {
        foreach ($itemsToDelete as $websiteCode => $websiteData) {
            $website = $this->websiteRepository->get($websiteCode);
            $this->websiteResource->delete($website);
        }
    }

    /**
     * @param array $itemsToDelete
     * @return void
     */
    private function deleteStores(array $itemsToDelete)
    {
        foreach ($itemsToDelete as $storeCode => $storeData) {
            $store = $this->storeRepository->get($storeCode);
            $this->storeResource->delete($store);
        }
    }

    /**
     * @param array $itemsToDelete
     * @return void
     */
    private function deleteGroups(array $itemsToDelete)
    {
        $groups = $this->groupRepository->getList();
        foreach ($itemsToDelete as $groupCode => $groupData) {
            /** @var \Magento\Store\Model\Group $group */
            foreach ($groups as $group) {
                if ($group->getCode() == $groupCode) {
                    $this->groupResource->delete($group);
                }
            }
        }
    }
}
