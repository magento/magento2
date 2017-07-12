<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\Config\Importer\Processor\ProcessorFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ResourceModel\Website;

/**
 * Imports stores, websites and groups from transmitted data.
 */
class Importer implements ImporterInterface
{
    /**
     * The data difference calculator.
     *
     * @var DataDifferenceCalculator
     */
    private $dataDifferenceCalculator;

    /**
     * The factory for processors.
     *
     * @var ProcessorFactory
     */
    private $processFactory;

    /**
     * The manager for operations with store.
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * The resource of transaction.
     *
     * @var Website
     */
    private $resource;

    /**
     * The application cache manager.
     *
     * @var CacheInterface
     */
    private $cacheManager;

    /**
     * @param DataDifferenceCalculator $dataDifferenceCalculator The factory for data difference calculators
     * @param ProcessorFactory $processFactory The factory for processes
     * @param StoreManagerInterface $storeManager The manager for operations with store
     * @param CacheInterface $cacheManager The application cache manager
     * @param Website $resource The resource of transaction
     */
    public function __construct(
        DataDifferenceCalculator $dataDifferenceCalculator,
        ProcessorFactory $processFactory,
        StoreManagerInterface $storeManager,
        CacheInterface $cacheManager,
        Website $resource
    ) {
        $this->dataDifferenceCalculator = $dataDifferenceCalculator;
        $this->processFactory = $processFactory;
        $this->storeManager = $storeManager;
        $this->cacheManager = $cacheManager;
        $this->resource = $resource;
    }

    /**
     * Imports the store data into the application.
     * After the import it flushes the store caches.
     *
     * {@inheritdoc}
     */
    public function import(array $data)
    {
        $actions = [
            ProcessorFactory::TYPE_CREATE,
            ProcessorFactory::TYPE_DELETE,
            ProcessorFactory::TYPE_UPDATE
        ];
        $messages = ['Stores were processed'];

        try {
            $newGroups = $this->getGroupsToCreate($data);

            if ($newGroups) {
                $messages[] = sprintf(
                    $this->getStoreGroupAssignMessage(),
                    implode(', ', array_column($newGroups, 'name'))
                );
            }

            $this->resource->beginTransaction();

            foreach ($actions as $action) {
                $this->processFactory->create($action)->run($data);
            }
        } catch (\Exception $exception) {
            $this->resource->rollBack();
            $this->reinitStores();

            throw new InvalidTransitionException(__('%1', $exception->getMessage()), $exception);
        }

        $this->resource->commit();
        $this->reinitStores();

        return $messages;
    }

    /**
     * Reinitialize store list.
     *
     * @return void
     */
    private function reinitStores()
    {
        $this->storeManager->reinitStores();
        $this->cacheManager->clean();
    }

    /**
     * Retrieves message reminder about root category assigning.
     *
     * @return string
     */
    private function getStoreGroupAssignMessage()
    {
        return 'The following new store groups must be associated with a root category: %s. '
            . PHP_EOL
            . 'Associate a store group with a root category in the Admin Panel: Stores > Settings > All Stores.';
    }

    /**
     * Checks which new store groups will be created.
     *
     * @param array $data The data set.
     * @return array
     */
    private function getGroupsToCreate(array $data)
    {
        if (!isset($data[ScopeInterface::SCOPE_GROUPS])) {
            return [];
        }

        $groups = $this->dataDifferenceCalculator->getItemsToCreate(
            ScopeInterface::SCOPE_GROUPS,
            $data[ScopeInterface::SCOPE_GROUPS]
        );

        return $groups;
    }

    /**
     * Retrieves all affected entities during the import procedure.
     *
     * {@inheritdoc}
     */
    public function getWarningMessages(array $data)
    {
        $messages = [];

        foreach ($data as $scope => $scopeData) {
            $messageMap = [
                'These %s will be deleted: %s' => $this->dataDifferenceCalculator->getItemsToDelete($scope, $scopeData),
                'These %s will be updated: %s' => $this->dataDifferenceCalculator->getItemsToUpdate($scope, $scopeData),
                'These %s will be created: %s' => $this->dataDifferenceCalculator->getItemsToCreate($scope, $scopeData),
            ];

            foreach ($messageMap as $message => $items) {
                if (!$items) {
                    continue;
                }

                $messages[] = $this->formatMessage($message, $items, $scope);
            }
        }

        return $messages;
    }

    /**
     * Formats message to appropriate format.
     *
     * @param string $message The message to display
     * @param array $items The items to be used
     * @param string $scope The given scope
     * @return string
     */
    private function formatMessage($message, array $items, $scope)
    {
        return sprintf(
            $message,
            ucfirst($scope),
            implode(', ', array_column($items, 'name'))
        );
    }
}
