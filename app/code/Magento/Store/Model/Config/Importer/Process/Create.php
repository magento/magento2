<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Process;

use Magento\Store\Model\Config\Importer\DataDifferenceFactory;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;

class Create
{
    /**
     * @var DataDifferenceFactory
     */
    private $dataDifferenceFactory;

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
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @param DataDifferenceFactory $dataDifferenceFactory
     * @param WebsiteFactory $websiteFactory
     * @param GroupFactory $groupFactory
     * @param StoreFactory $storeFactory
     * @param Website $websiteResource
     * @param Store $storeResource
     * @param Group $groupResource
     */
    public function __construct(
        DataDifferenceFactory $dataDifferenceFactory,
        WebsiteFactory $websiteFactory,
        GroupFactory $groupFactory,
        StoreFactory $storeFactory,
        Website $websiteResource,
        Store $storeResource,
        Group $groupResource
    ) {
        $this->dataDifferenceFactory = $dataDifferenceFactory;
        $this->websiteFactory = $websiteFactory;
        $this->groupFactory = $groupFactory;
        $this->storeFactory = $storeFactory;
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
        foreach (['websites', 'groups', 'stores'] as $scope) {
            $dataDifference = $this->dataDifferenceFactory->create($scope);
            $itemsToCreate = $dataDifference->getItemsToCreate($data[$scope]);

            if (empty($itemsToCreate)) {
                continue;
            }

            try {
                if ($scope == 'websites') {
                    $this->createWebsites($itemsToCreate);
                } elseif ($scope == 'groups') {
                    $this->createGroups($itemsToCreate, $data);
                } elseif ($scope == 'stores') {
                    $this->createStores($itemsToCreate);
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $itemsToCreate
     * @throws
     */
    private function createWebsites(array $itemsToCreate)
    {
        foreach ($itemsToCreate as $websiteData) {
            unset($websiteData['website_id']);
            $website = $this->websiteFactory->create();
            $website->setData($websiteData);
            $this->websiteResource->save($website);
        }
    }

    /**
     * @param array $itemsToCreate
     * @param array $data
     * @throws \Exception
     */
    private function createGroups(array $itemsToCreate, array $data)
    {
        foreach ($itemsToCreate as $groupData) {
            $websiteId = $groupData['website_id'];

            // Find Website Code from $data array
            // $websiteCode = $data['websites']
            // Load Website By Code
            // Set Website Id to group

            unset($groupData['group_id'], $groupData['website_id']);
            $group = $this->websiteFactory->create();
            $group->setData($groupData);
            $this->groupResource->save($group);
        }
    }

    /**
     * @param $itemsToCreate
     */
    private function createStores($itemsToCreate)
    {
    }
}
