<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Processor;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\Config\Importer\Processor\ProcessorInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;

/**
 * @inheritdoc
 */
class Update implements ProcessorInterface
{
    /**
     * @var DataDifferenceCalculator
     */
    private $dataDifferenceCalculator;

    /**
     * @var Website
     */
    private $websiteResource;

    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var Store
     */
    private $storeResource;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var Group
     */
    private $groupResource;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @param DataDifferenceCalculator $dataDifferenceCalculator
     * @param Website $websiteResource
     * @param WebsiteFactory $websiteFactory
     * @param Store $storeResource
     * @param StoreFactory $storeFactory
     * @param Group $groupResource
     * @param GroupFactory $groupFactory
     */
    public function __construct(
        DataDifferenceCalculator $dataDifferenceCalculator,
        Website $websiteResource,
        WebsiteFactory $websiteFactory,
        Store $storeResource,
        StoreFactory $storeFactory,
        Group $groupResource,
        GroupFactory $groupFactory
    ) {
        $this->dataDifferenceCalculator = $dataDifferenceCalculator;
        $this->websiteResource = $websiteResource;
        $this->websiteFactory = $websiteFactory;
        $this->storeResource = $storeResource;
        $this->storeFactory = $storeFactory;
        $this->groupResource = $groupResource;
        $this->groupFactory = $groupFactory;
    }

    /**
     * @inheritdoc
     */
    public function run(array $data)
    {
        try {
            $entities = [
                ScopeInterface::SCOPE_WEBSITES,
                ScopeInterface::SCOPE_GROUPS,
                ScopeInterface::SCOPE_STORES,
            ];

            foreach ($entities as $scope) {
                $items = $this->dataDifferenceCalculator->getItemsToUpdate($scope, $data[$scope]);

                if (!$items) {
                    continue;
                }

                switch ($scope) {
                    case ScopeInterface::SCOPE_WEBSITES:
                        $this->updateWebsites($items);
                        break;
                    case ScopeInterface::SCOPE_STORES:
                        $this->updateStores($items, $data);
                        break;
                    case ScopeInterface::SCOPE_GROUPS:
                        $this->updateGroups($items, $data);
                }
            }

        } catch (\Exception $exception) {
            throw new RuntimeException(__('%1', $exception->getMessage()), $exception);
        }
    }

    /**
     * Updates websites with a new data.
     *
     * @param array $data The data to be updated
     * @return void
     */
    private function updateWebsites(array $data)
    {
        foreach ($data as $code => $websiteData) {
            unset(
                $websiteData['website_id'],
                $websiteData['default_group_id']
            );

            $website = $this->websiteFactory->create();
            $this->websiteResource->load($website, $code, 'code');

            $website->setData(array_replace($website->getData(), $websiteData));
            $this->websiteResource->save($website);
        }
    }

    /**
     * Updates stores with a new data.
     *
     * @param array $items The items to update
     * @param array $data The data to be updated
     * @return void
     */
    private function updateStores(array $items, array $data)
    {
        foreach ($items as $code => $storeData) {
            $groupId = $storeData['group_id'];
            $websiteId = $storeData['website_id'];

            unset(
                $storeData['store_id'],
                $storeData['website_id'],
                $storeData['group_id']
            );

            $store = $this->storeFactory->create();
            $group = $this->findGroupById($data, $groupId);
            $website = $this->findWebsiteById($data, $websiteId);

            $this->storeResource->load($store, $code, 'code');

            $store->setData(array_replace($store->getData(), $storeData));

            if ($website && $website->getId() != $store->getWebsiteId()) {
                $store->setWebsite($website);
            }

            if ($group && $group->getId() != $store->getGroupId()) {
                $store->setGroup($group);
            }

            $this->storeResource->save($store);
        }
    }

    /**
     * Updates groups with a new data.
     *
     * @param array $items The items to update
     * @param array $data The data to be updated
     * @throws CouldNotSaveException If group could not be saved
     * @return void
     */
    private function updateGroups(array $items, array $data)
    {
        foreach ($items as $code => $groupData) {
            $websiteId = $groupData['website_id'];

            unset($groupData['group_id'], $groupData['website_id']);

            $group = $this->groupFactory->create();
            $website = $this->findWebsiteById($data, $websiteId);

            $this->groupResource->load($group, $code, 'code');

            $group->setData(array_replace($group->getData(), $groupData));

            if ($website && $website->getId() != $group->getWebsiteId()) {
                $group->setWebsite($website);
            }

            $this->groupResource->save($group);
        }
    }

    /**
     * Searches through given websites and compares with current websites.
     * Returns found website.
     *
     * @param array $data The data to be searched in
     * @param string $websiteId The website id
     * @return \Magento\Store\Model\Website|null
     */
    private function findWebsiteById(array $data, $websiteId)
    {
        foreach ($data[ScopeInterface::SCOPE_WEBSITES] as $websiteData) {
            if ($websiteId == $websiteData['website_id']) {
                $website = $this->websiteFactory->create();
                $this->websiteResource->load($website, $websiteData['code'], 'code');

                return $website;
            }
        }

        return null;
    }

    /**
     * Searches through given groups and compares with current websites.
     * Returns found group.
     *
     * @param array $data The data to be searched in
     * @param string $groupId The group id
     * @return \Magento\Store\Model\Group|null
     */
    private function findGroupById(array $data, $groupId)
    {
        foreach ($data[ScopeInterface::SCOPE_GROUPS] as $groupData) {
            if ($groupId == $groupData['group_id']) {
                $group = $this->groupFactory->create();
                $this->groupResource->load($group, $groupData['code'], 'code');

                return $group;
            }
        }

        return null;
    }
}
