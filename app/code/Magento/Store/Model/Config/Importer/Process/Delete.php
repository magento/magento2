<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Process;

use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Registry;
use Magento\Store\Model\Config\Importer\DataDifferenceFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\WebsiteRepository;
use Magento\Store\Model\GroupRepository;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;

/**
 * @inheritdoc
 */
class Delete implements ProcessInterface
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
     * @inheritdoc
     */
    public function run(array $data)
    {
        try {
            if (!$this->registry->registry('isSecureArea')) {
                $this->registry->register('isSecureArea', true);
            }

            /**
             * Remove records that not exists in import configuration.
             * First must be removed groups and stores, then websites.
             */
            $entities = [
                ScopeInterface::SCOPE_GROUPS,
                ScopeInterface::SCOPE_STORES,
                ScopeInterface::SCOPE_WEBSITES
            ];

            foreach ($entities as $scope) {
                $dataDifference = $this->dataDifferenceFactory->create($scope);
                $items = $dataDifference->getItemsToDelete($data[$scope]);

                if (!$items) {
                    continue;
                }

                switch ($scope) {
                    case ScopeInterface::SCOPE_WEBSITES:
                        $this->deleteWebsites($items);
                        break;
                    case ScopeInterface::SCOPE_STORES:
                        $this->deleteStores($items);
                        break;
                    case ScopeInterface::SCOPE_GROUPS:
                        $this->deleteGroups($items);
                }
            }
        } catch (\Exception $e) {
            throw new RuntimeException(__('%1', $e->getMessage()), $e);
        }
    }

    /**
     * Deletes websites from application.
     *
     * @param array $items The websites to delete
     * @return void
     */
    private function deleteWebsites(array $items)
    {
        $items = array_keys($items);

        foreach ($items as $websiteCode) {
            $this->websiteResource->delete(
                $this->websiteRepository->get($websiteCode)
            );
        }
    }

    /**
     * Deletes stores from application.
     *
     * @param array $items The stores to delete
     * @return void
     */
    private function deleteStores(array $items)
    {
        $items = array_keys($items);

        foreach ($items as $storeCode) {
            $this->storeResource->delete(
                $this->storeRepository->get($storeCode)
            );
        }
    }

    /**
     * Deletes groups from application.
     *
     * @param array $items The groups to delete
     * @return void
     */
    private function deleteGroups(array $items)
    {
        $groups = $this->groupRepository->getList();
        $items = array_keys($items);

        foreach ($items as $groupCode) {
            /** @var \Magento\Store\Model\Group $group */
            foreach ($groups as $group) {
                if ($group->getCode() == $groupCode) {
                    $this->groupResource->delete($group);
                }
            }
        }
    }
}
