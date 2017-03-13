<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Store\App\Config\Source\RuntimeConfigSource;
use Magento\Store\Model\Config\Importer\DataDifferenceFactory;
use Magento\Store\Model\Config\Importer\Process\ProcessFactory;
use Magento\Store\Model\StoreManager;

/**
 * Imports stores, websites and groups from configuration files.
 */
class Importer implements ImporterInterface
{
    /**
     * @var RuntimeConfigSource
     */
    private $runtimeConfigSource;

    /**
     * @var DataDifferenceFactory
     */
    private $dataDifferenceFactory;

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @param RuntimeConfigSource $runtimeConfigSource
     * @param DataDifferenceFactory $dataDifferenceFactory
     * @param ProcessFactory $processFactory
     * @param StoreManager $storeManager
     */
    public function __construct(
        RuntimeConfigSource $runtimeConfigSource,
        DataDifferenceFactory $dataDifferenceFactory,
        ProcessFactory $processFactory,
        StoreManager $storeManager
    ) {
        $this->runtimeConfigSource = $runtimeConfigSource;
        $this->dataDifferenceFactory = $dataDifferenceFactory;
        $this->processFactory = $processFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function import(array $data)
    {
        try {
            $actions = [
                ProcessFactory::TYPE_CREATE,
                ProcessFactory::TYPE_DELETE,
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
     * @inheritdoc
     */
    public function getWarningMessages(array $data)
    {
        $messages = [];

        foreach ($data as $scope => $scopeData) {
            $dataDifference = $this->dataDifferenceFactory->create($scope);

            $messageMap = [
                'Next %s will be deleted: %s' => $dataDifference->getItemsToDelete($scopeData),
                'Next %s will be updated: %s' => $dataDifference->getItemsToUpdate($scopeData),
                'Next %s will be created: %s' => $dataDifference->getItemsToCreate($scopeData),
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
     * @param string $message The message
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
