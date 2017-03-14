<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\Config\Importer\Process\ProcessFactory;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Imports stores, websites and groups from configuration files.
 */
class Importer implements ImporterInterface
{
    /**
     * The factory for data difference calculators.
     *
     * @var DataDifferenceCalculator
     */
    private $dataDifferenceCalculator;

    /**
     * The factory for processes.
     *
     * @var ProcessFactory
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
     * @param ProcessFactory $processFactory The factory for processes
     * @param StoreManagerInterface $storeManager The manager for operations with store
     * @param CacheInterface $cacheManager The application cache manager
     * @param Website $resource The resource of transaction
     */
    public function __construct(
        DataDifferenceCalculator $dataDifferenceCalculator,
        ProcessFactory $processFactory,
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
        try {
            $actions = [
                ProcessFactory::TYPE_DELETE,
                ProcessFactory::TYPE_CREATE,
                ProcessFactory::TYPE_UPDATE
            ];

            $this->resource->getConnection()->beginTransaction();

            foreach ($actions as $action) {
                $this->processFactory->create($action)->run($data);
            }

            $this->resource->getConnection()->commit();
        } catch (LocalizedException $exception) {
            $this->resource->getConnection()->rollBack();

            throw new InvalidTransitionException(__('%1', $exception->getMessage()), $exception);
        } finally {
            $this->storeManager->reinitStores();
            $this->cacheManager->clean();
        }

        return ['Stores were processed'];
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
                'Next %s will be deleted: %s' => $this->dataDifferenceCalculator->getItemsToDelete($scope, $scopeData),
                'Next %s will be updated: %s' => $this->dataDifferenceCalculator->getItemsToUpdate($scope, $scopeData),
                'Next %s will be created: %s' => $this->dataDifferenceCalculator->getItemsToCreate($scope, $scopeData),
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
