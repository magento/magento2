<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Processor;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Registry;
use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\Group;
use Magento\Store\Model\ResourceModel\Group\Collection;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\WebsiteRepository;

/**
 * The processor for deleting different entities.
 *
 * {@inheritdoc}
 */
class Delete implements ProcessorInterface
{
    /**
     * The calculator for data differences.
     *
     * @var DataDifferenceCalculator
     */
    private $dataDifferenceCalculator;

    /**
     * The repository for websites.
     *
     * @var WebsiteRepository
     */
    private $websiteRepository;

    /**
     * The repository for stores.
     *
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * The collection of store groups.
     *
     * @var Collection
     */
    private $groupCollection;

    /**
     * The application registry.
     *
     * @var Registry
     */
    private $registry;

    /**
     * The event manager.
     *
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param Registry $registry The application registry The application registry
     * @param DataDifferenceCalculator $dataDifferenceCalculator The calculator for data differences
     * @param ManagerInterface $eventManager The event manager
     * @param WebsiteRepository $websiteRepository The repository for websites
     * @param StoreRepository $storeRepository The repository for stores
     * @param Collection $groupCollection The collection of store groups
     */
    public function __construct(
        Registry $registry,
        DataDifferenceCalculator $dataDifferenceCalculator,
        ManagerInterface $eventManager,
        WebsiteRepository $websiteRepository,
        StoreRepository $storeRepository,
        Collection $groupCollection
    ) {
        $this->registry = $registry;
        $this->dataDifferenceCalculator = $dataDifferenceCalculator;
        $this->eventManager = $eventManager;
        $this->websiteRepository = $websiteRepository;
        $this->storeRepository = $storeRepository;
        $this->groupCollection = $groupCollection;
    }

    /**
     * Deletes entities from application according to the data set.
     *
     * {@inheritdoc}
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
                if (!isset($data[$scope])) {
                    continue;
                }

                $items = $this->dataDifferenceCalculator->getItemsToDelete($scope, $data[$scope]);

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
                        break;
                }
            }
        } catch (\Exception $e) {
            throw new RuntimeException(__('%1', $e->getMessage()), $e);
        } finally {
            $this->registry->unregister('isSecureArea');
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
            $website = $this->websiteRepository->get($websiteCode);
            $website->getResource()->delete($website);
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
            $store = $this->storeRepository->get($storeCode);
            $store->getResource()->delete($store);
            $store->getResource()->addCommitCallback(function () use ($store) {
                $this->eventManager->dispatch('store_delete', ['store' => $store]);
            });
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
        $this->groupCollection->addFieldToFilter('code', ['in' => array_keys($items)]);
        /** @var Group $group */
        foreach ($this->groupCollection as $group) {
            $group->getResource()->delete($group);
        }
    }
}
