<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Process;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Store\Model\Config\Importer\DataDifferenceFactory;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;

/**
 * @inheritdoc
 */
class Create implements ProcessInterface
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
                $dataDifference = $this->dataDifferenceFactory->create($scope);
                $itemsToCreate = $dataDifference->getItemsToCreate($data[$scope]);

                if (!$itemsToCreate) {
                    continue;
                }

                switch ($scope) {
                    case ScopeInterface::SCOPE_WEBSITES:
                        $this->createWebsites($itemsToCreate);
                        break;
                    case ScopeInterface::SCOPE_GROUPS:
                        $this->createGroups($itemsToCreate, $data);
                        break;
                    case ScopeInterface::SCOPE_STORES:
                        $this->createStores($itemsToCreate);
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
            unset($websiteData['website_id']);
            $website = $this->websiteFactory->create();
            $website->setData($websiteData);
            $this->websiteResource->save($website);
        }
    }

    /**
     * Creates groups from the data.
     *
     * @param array $items Groups to create
     * @param array $data The all available data
     * @return void
     * @throws \Exception
     */
    private function createGroups(array $items, array $data)
    {
        foreach ($items as $groupData) {
            $websiteId = $groupData['website_id'];

            unset($groupData['group_id'], $groupData['website_id']);

            $website = $this->detectWebsiteByCodeId(
                $data,
                $websiteId
            );

            $group = $this->groupFactory->create();
            $group->setData($groupData);
            $group->setWebsite($website);

            $this->groupResource->save($group);
        }
    }

    /**
     * Searches through given websites and compares with current websites.
     * Return found website.
     *
     * @param array $data
     * @param string $websiteId
     * @return \Magento\Store\Model\Website
     * @throws NotFoundException
     */
    private function detectWebsiteByCodeId(array $data, $websiteId)
    {
        foreach ($data['websites'] as $websiteData) {
            if ($websiteId == $websiteData['website_id']) {
                $website = $this->websiteFactory->create();
                $this->websiteResource->load($website, $website['code'], 'code');

                return $website;
            }
        }

        throw new NotFoundException(__('Website was not found'));
    }

    /**
     * Creates stores from the given data.
     *
     * @param array $items Stores to create
     * @return void
     */
    private function createStores(array $items)
    {
        foreach ($items as $storeData) {
            unset($storeData['store_id']);

            $store = $this->storeFactory->create();
            $store->setData($storeData);

            $this->storeResource->save($store);
        }
    }
}
