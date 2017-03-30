<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * The processor for creating of new entities.
 *
 * {@inheritdoc}
 */
class Create implements ProcessorInterface
{
    /**
     * The calculator for data differences.
     *
     * @var DataDifferenceCalculator
     */
    private $dataDifferenceCalculator;

    /**
     * The factory for website entity.
     *
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * The factory for group entity.
     *
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * The factory for store entity.
     *
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * The event manager.
     *
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param DataDifferenceCalculator $dataDifferenceCalculator The calculator for data differences
     * @param ManagerInterface $eventManager The event manager
     * @param WebsiteFactory $websiteFactory The factory for website entity
     * @param GroupFactory $groupFactory The factory for group entity
     * @param StoreFactory $storeFactory The factory for store entity
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
     * Creates entities in application according to the data set.
     *
     * {@inheritdoc}
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
                        $this->createWebsites($items, $data);
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
     * @param array $data The all available data
     * @return void
     */
    private function createWebsites(array $items, array $data)
    {
        foreach ($items as $websiteData) {
            unset($websiteData['website_id']);

            $website = $this->websiteFactory->create();
            $website->setData($websiteData);
            $website->getResource()->save($website);

            $website->getResource()->addCommitCallback(function () use ($website, $data) {
                $website->setDefaultGroupId(
                    $this->detectGroupById($data, $website->getDefaultGroupId())->getId()
                );
                $website->getResource()->save($website);
            });
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
                $groupData['website_id']
            );

            $website = $this->detectWebsiteById(
                $data,
                $websiteId
            );

            $group = $this->groupFactory->create();
            $group->setData($groupData);
            $group->setRootCategoryId(0);

            $group->getResource()->save($group);
            $group->getResource()->addCommitCallback(function () use ($data, $group, $website) {
                $store = $this->detectStoreById($data, (int)$group->getDefaultStoreId());
                $group->setDefaultStoreId($store->getStoreId());
                $group->setWebsite($website);
                $group->getResource()->save($group);

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
            $websiteId = $storeData['website_id'];

            unset(
                $storeData['store_id'],
                $storeData['website_id'],
                $storeData['group_id']
            );

            $group = $this->detectGroupById($data, $groupId);
            $website = $this->detectWebsiteById($data, $websiteId);

            $store = $this->storeFactory->create();
            $store->setData($storeData);

            $store->getResource()->save($store);
            $store->getResource()->addCommitCallback(function () use ($store, $group, $website) {
                $store->setGroup($group);
                $store->setWebsite($website);
                $store->getResource()->save($store);

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

    /**
     * Searches through given stores and compares with current stores.
     * Returns found store.
     *
     * @param array $data The data to be searched in
     * @param string $storeId The store id
     * @return \Magento\Store\Model\Store
     * @throws NotFoundException If store was not detected
     */
    private function detectStoreById(array $data, $storeId)
    {
        foreach ($data[ScopeInterface::SCOPE_STORES] as $storeData) {
            if ($storeId == $storeData['store_id']) {
                $store = $this->storeFactory->create();
                $store->getResource()->load($store, $storeData['code'], 'code');

                return $store;
            }
        }

        throw new NotFoundException(__('Store was not found'));
    }
}
