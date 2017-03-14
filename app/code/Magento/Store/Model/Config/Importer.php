<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\Config\Importer\Process\ProcessFactory;
use Magento\Store\Model\StoreManager;

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
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @param DataDifferenceCalculator $dataDifferenceCalculator The factory for data difference calculators
     * @param ProcessFactory $processFactory The factory for processes
     * @param StoreManager $storeManager The manager for operations with store
     */
    public function __construct(
        DataDifferenceCalculator $dataDifferenceCalculator,
        ProcessFactory $processFactory,
        StoreManager $storeManager
    ) {
        $this->dataDifferenceCalculator = $dataDifferenceCalculator;
        $this->processFactory = $processFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Imports the store data into the application.
     * After the import it flushes the store cached state.
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

            foreach ($actions as $action) {
                $this->processFactory->create($action)->run($data);
            }

            $this->storeManager->reinitStores();
        } catch (LocalizedException $exception) {
            throw new InvalidTransitionException(__('%1', $exception->getMessage()), $exception);
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
