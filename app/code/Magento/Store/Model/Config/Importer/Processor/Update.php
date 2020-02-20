<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Processor;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;
use Magento\Framework\Event\ManagerInterface;

/**
 * The process for updating of existing entities.
 *
 * {@inheritdoc}
 */
class Update implements ProcessorInterface
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
     * The factory for store entity.
     *
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * The factory for group entity.
     *
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * The event manager.
     *
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param DataDifferenceCalculator $dataDifferenceCalculator The calculator for data differences
     * @param WebsiteFactory $websiteFactory The factory for website entity
     * @param StoreFactory $storeFactory The factory for store entity
     * @param GroupFactory $groupFactory The factory for group entity
     * @param ManagerInterface $eventManager The event manager
     */
    public function __construct(
        DataDifferenceCalculator $dataDifferenceCalculator,
        WebsiteFactory $websiteFactory,
        StoreFactory $storeFactory,
        GroupFactory $groupFactory,
        ManagerInterface $eventManager
    ) {
        $this->dataDifferenceCalculator = $dataDifferenceCalculator;
        $this->websiteFactory = $websiteFactory;
        $this->storeFactory = $storeFactory;
        $this->groupFactory = $groupFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * Updates entities in application according to the data set.
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
                if (!isset($data[$scope])) {
                    continue;
                }

                $items = $this->dataDifferenceCalculator->getItemsToUpdate($scope, $data[$scope]);

                if (!$items) {
                    continue;
                }

                switch ($scope) {
                    case ScopeInterface::SCOPE_WEBSITES:
                        $this->updateWebsites($items, $data);
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
     * @param array $items The items to update
     * @param array $data The data to be updated
     * @return void
     */
    private function updateWebsites(array $items, array $data)
    {
        foreach ($items as $code => $websiteData) {
            unset($websiteData['website_id']);

            $website = $this->websiteFactory->create();
            $website->getResource()->load($website, $code, 'code');
            $website->setData(array_replace($website->getData(), $websiteData));

            $group = $this->findGroupById($data, $website->getDefaultGroupId());
            $website->setDefaultGroupId($group->getGroupId());

            $website->getResource()->save($website);
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

            $store->getResource()->load($store, $code, 'code');

            $store->setData(array_replace($store->getData(), $storeData));

            if ($website && $website->getId() != $store->getWebsiteId()) {
                $store->setWebsite($website);
            }

            if ($group && $group->getId() != $store->getGroupId()) {
                $store->setGroup($group);
            }

            $store->save();
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

            unset(
                $groupData['group_id'],
                $groupData['website_id'],
                $groupData['root_category_id']
            );

            $website = $this->findWebsiteById($data, $websiteId);

            $group = $this->groupFactory->create();
            $group->getResource()->load($group, $code, 'code');
            $group->setData(array_replace($group->getData(), $groupData));

            $store = $this->findStoreById($data, $group->getDefaultStoreId());
            $group->setDefaultStoreId($store->getStoreId());

            if ($website && $website->getId() != $group->getWebsiteId()) {
                $group->setWebsite($website);
            }
            $group->save();
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
                $website->getResource()->load($website, $websiteData['code'], 'code');

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
                $group->getResource()->load($group, $groupData['code'], 'code');

                return $group;
            }
        }

        return null;
    }

    /**
     * Searches through given stores and compares with current stores.
     * Returns found store.
     *
     * @param array $data The data to be searched in
     * @param string $storeId The store id
     * @return \Magento\Store\Model\Store|null
     */
    private function findStoreById(array $data, $storeId)
    {
        foreach ($data[ScopeInterface::SCOPE_STORES] as $storeData) {
            if ($storeId == $storeData['store_id']) {
                $store = $this->storeFactory->create();
                $store->getResource()->load($store, $storeData['code'], 'code');

                return $store;
            }
        }

        return null;
    }
}
