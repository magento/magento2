<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Processor;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;

/**
 * @inheritdoc
 */
class Create implements ProcessorInterface
{
    /**
     * @var DataDifferenceCalculator
     */
    private $dataDifferenceCalculator;

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
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param DataDifferenceCalculator $dataDifferenceCalculator
     * @param ManagerInterface $eventManager
     * @param WebsiteFactory $websiteFactory
     * @param GroupFactory $groupFactory
     * @param StoreFactory $storeFactory
     */
    public function __construct(
        DataDifferenceCalculator $dataDifferenceCalculator,
        ManagerInterface $eventManager,
        WebsiteFactory $websiteFactory,
        GroupFactory $groupFactory,
        StoreFactory $storeFactory
    ) {
        $this->dataDifferenceCalculator = $dataDifferenceCalculator;
        $this->websiteFactory = $websiteFactory;
        $this->groupFactory = $groupFactory;
        $this->storeFactory = $storeFactory;
        $this->eventManager = $eventManager;
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
                $items = $this->dataDifferenceCalculator->getItemsToCreate($scope, $data[$scope]);

                if (!$items) {
                    continue;
                }

                switch ($scope) {
                    case ScopeInterface::SCOPE_WEBSITES:
                        $this->createWebsites($items);
                        break;
                    case ScopeInterface::SCOPE_GROUPS:
                        $this->createGroups($items, $data);
                        break;
                    case ScopeInterface::SCOPE_STORES:
                        $this->createStores($items, $data);
                        break;
                }
            }
        } catch (\Exception $e) {
            throw new RuntimeException(__('%1', $e->getMessage()), $e);
        }
    }

    /**
     * Creates websites from the data.
     *
     * @param array $items Websites to create
     * @return void
     */
    private function createWebsites(array $items)
    {
        foreach ($items as $websiteData) {
            unset(
                $websiteData['website_id'],
                $websiteData['default_group_id']
            );
            $website = $this->websiteFactory->create();
            $website->setData($websiteData);
            $website->getResource()->save($website);
        }
    }

    /**
     * Creates groups from the data.
     *
     * @param array $items Groups to create
     * @param array $data The all available data
     * @return void
     */
    private function createGroups(array $items, array $data)
    {
        foreach ($items as $groupData) {
            $websiteId = $groupData['website_id'];

            unset(
                $groupData['group_id'],
                $groupData['website_id'],
                $groupData['default_store_id']
            );

            $website = $this->detectWebsiteById(
                $data,
                $websiteId
            );

            $group = $this->groupFactory->create();
            $group->setData($groupData);
            $group->setWebsite($website);
            $group->setRootCategoryId(0);

            $group->getResource()->save($group);
            $group->getResource()->addCommitCallback(function () use ($group) {
                $this->eventManager->dispatch('store_group_save', ['group' => $group]);
            });
        }
    }

    /**
     * Creates stores from the given data.
     *
     * @param array $items Stores to create
     * @param array $data The all available data
     * @return void
     */
    private function createStores(array $items, array $data)
    {
        foreach ($items as $storeData) {
            $groupId = $storeData['group_id'];

            unset(
                $storeData['store_id'],
                $storeData['website_id'],
                $storeData['group_id']
            );

            $group = $this->detectGroupById(
                $data,
                $groupId
            );

            $store = $this->storeFactory->create();
            $store->setData($storeData);
            $store->setGroup($group);

            $store->getResource()->save($store);
            $store->getResource()->addCommitCallback(function () use ($store) {
                $this->eventManager->dispatch('store_add', ['store' => $store]);
            });
        }
    }

    /**
     * Searches through given websites and compares with current websites.
     * Returns found website.
     *
     * @param array $data The data to be searched in
     * @param string $websiteId The website id
     * @return \Magento\Store\Model\Website
     * @throws NotFoundException If website was not detected
     */
    private function detectWebsiteById(array $data, $websiteId)
    {
        foreach ($data[ScopeInterface::SCOPE_WEBSITES] as $websiteData) {
            if ($websiteId == $websiteData['website_id']) {
                $website = $this->websiteFactory->create();
                $website->getResource()->load($website, $websiteData['code'], 'code');

                return $website;
            }
        }

        throw new NotFoundException(__('Website was not found'));
    }

    /**
     * Searches through given groups and compares with current websites.
     * Returns found group.
     *
     * @param array $data The data to be searched in
     * @param string $groupId The group id
     * @return \Magento\Store\Model\Group
     * @throws NotFoundException If group was not detected
     */
    private function detectGroupById(array $data, $groupId)
    {
        foreach ($data[ScopeInterface::SCOPE_GROUPS] as $groupData) {
            if ($groupId == $groupData['group_id']) {
                $group = $this->groupFactory->create();
                $group->getResource()->load($group, $groupData['code'], 'code');

                return $group;
            }
        }

        throw new NotFoundException(__('Group was not found'));
    }
}
