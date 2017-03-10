<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Store\App\Config\Source\RuntimeConfigSource;
use Magento\Store\Model\Config\Importer\DataDifferenceFactory;
use Magento\Store\Model\Config\Importer\Process\Create;
use Magento\Store\Model\Config\Importer\Process\Remove;

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
     * @var Remove
     */
    private $removeProcess;

    /**
     * @var Create
     */
    private $createProcess;

    /**
     * @param RuntimeConfigSource $runtimeConfigSource
     * @param DataDifferenceFactory $dataDifferenceFactory
     * @param Remove $removeProcess
     * @param Create $createProcess
     */
    public function __construct(
        RuntimeConfigSource $runtimeConfigSource,
        DataDifferenceFactory $dataDifferenceFactory,
        Remove $removeProcess,
        Create $createProcess
    ) {
        $this->runtimeConfigSource = $runtimeConfigSource;
        $this->dataDifferenceFactory = $dataDifferenceFactory;
        $this->removeProcess = $removeProcess;
        $this->createProcess = $createProcess;
    }

    /**
     * @inheritdoc
     */
    public function import(array $data)
    {
        // Remove records
        $this->removeProcess->run($data);

        // Create new records
        $this->createProcess->run($data);

        // Update changed records
    }

    /**
     * @inheritdoc
     */
    public function getWarningMessages(array $data)
    {
        $messages = [];

        foreach ($data as $scope => $scopeData) {
            $dataDifference = $this->dataDifferenceFactory->create($scope);

            $itemsToDelete = $dataDifference->getItemsToDelete($scopeData);
            if (count($itemsToDelete)) {
                $messages[] = sprintf(
                    'Next %s will be deleted: %s',
                    ucfirst($scope),
                    implode(', ', array_column($itemsToDelete, 'name'))
                );
            }

            $itemsToUpdate = $dataDifference->getItemsToUpdate($scopeData);
            if (count($itemsToUpdate)) {
                $messages[] = sprintf(
                    'Next %s will be updated: %s',
                    ucfirst($scope),
                    implode(', ', array_column($itemsToUpdate, 'name'))
                );
            }

            $itemsToCreate = $dataDifference->getItemsToCreate($scopeData);
            if (count($itemsToCreate)) {
                $messages[] = sprintf(
                    'Next %s will be created: %s',
                    ucfirst($scope),
                    implode(', ', array_column($itemsToCreate, 'name'))
                );
            }
        }

        return $messages;
    }
}
