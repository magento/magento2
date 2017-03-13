<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Process;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Store\Model\Config\Importer\DataDifferenceFactory;
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
class Update implements ProcessInterface
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
     * @param DataDifferenceFactory $dataDifferenceFactory
     * @param Website $websiteResource
     * @param WebsiteFactory $websiteFactory
     * @param Store $storeResource
     * @param StoreFactory $storeFactory
     * @param Group $groupResource
     * @param GroupFactory $groupFactory
     */
    public function __construct(
        DataDifferenceFactory $dataDifferenceFactory,
        Website $websiteResource,
        WebsiteFactory $websiteFactory,
        Store $storeResource,
        StoreFactory $storeFactory,
        Group $groupResource,
        GroupFactory $groupFactory
    ) {
        $this->dataDifferenceFactory = $dataDifferenceFactory;
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
                $dataDifference = $this->dataDifferenceFactory->create($scope);
                $items = $dataDifference->getItemsToUpdate($data[$scope]);

                if (!$items) {
                    continue;
                }

                switch ($scope) {
                    case ScopeInterface::SCOPE_WEBSITES:
                        $this->updateWebsites($items);
                        break;
                    case ScopeInterface::SCOPE_STORES:
                        $this->updateStores($items);
                        break;
                    case ScopeInterface::SCOPE_GROUPS:
                        $this->updateGroups($items);
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
            unset($websiteData['website_id'], $websiteData['code']);

            $website = $this->websiteFactory->create();
            $this->websiteResource->load($website, $code, 'code');

            $website->setData(array_replace($website->getData(), $websiteData));
            $this->websiteResource->save($website);
        }
    }

    /**
     * Updates stores with a new data.
     *
     * @param array $data The data to be updated
     * @return void
     */
    private function updateStores(array $data)
    {
        foreach ($data as $code => $storeData) {
            unset($storeData['store_id']);

            $store = $this->storeFactory->create();
            $this->storeResource->load($store, $code, 'code');

            $store->setData(array_replace($store->getData(), $storeData));
            $this->storeResource->save($store);
        }
    }

    /**
     * Updates groups with a new data.
     *
     * @param array $data The data to be updated
     * @throws CouldNotSaveException If group can not be saved
     * @return void
     */
    private function updateGroups(array $data)
    {
        foreach ($data as $code => $groupData) {
            unset($groupData['group_id']);

            $group = $this->groupFactory->create();
            $this->groupResource->load($group, $code, 'code');

            if (!empty($groupData['default_store_id'])) {
                $defaultStoreId = $groupData['default_store_id'];

                if (
                    !empty($group->getStores()[$defaultStoreId]) &&
                    !$group->getStores()[$defaultStoreId]->isActive()
                ) {
                    throw new CouldNotSaveException(
                        __('An inactive store view cannot be saved as default store view')
                    );
                }
            }

            $group->setData(
                array_replace(
                    $group->getData(),
                    $groupData
                )
            );

            $this->groupResource->save($group);
        }
    }
}
