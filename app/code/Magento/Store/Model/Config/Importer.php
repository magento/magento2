<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Store\App\Config\Source\RuntimeConfigSource;
use Magento\Store\Model\Config\Importer\DataDifferenceFactory;
use Magento\Store\Model\Config\Importer\Process\ProcessFactory;

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
     * @param RuntimeConfigSource $runtimeConfigSource
     * @param DataDifferenceFactory $dataDifferenceFactory
     * @param ProcessFactory $processFactory
     */
    public function __construct(
        RuntimeConfigSource $runtimeConfigSource,
        DataDifferenceFactory $dataDifferenceFactory,
        ProcessFactory $processFactory
    ) {
        $this->runtimeConfigSource = $runtimeConfigSource;
        $this->dataDifferenceFactory = $dataDifferenceFactory;
        $this->processFactory = $processFactory;
    }

    /**
     * @inheritdoc
     */
    public function import(array $data)
    {
        // Remove records
        $this->processFactory->create(ProcessFactory::TYPE_CREATE)->run($data);

        // Create new records
        $this->processFactory->create(ProcessFactory::TYPE_DELETE)->run($data);

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
            if ($itemsToDelete) {
                $messages[] = sprintf(
                    'Next %s will be deleted: %s',
                    ucfirst($scope),
                    implode(', ', array_column($itemsToDelete, 'name'))
                );
            }

            $itemsToUpdate = $dataDifference->getItemsToUpdate($scopeData);
            if ($itemsToUpdate) {
                $messages[] = sprintf(
                    'Next %s will be updated: %s',
                    ucfirst($scope),
                    implode(', ', array_column($itemsToUpdate, 'name'))
                );
            }

            $itemsToCreate = $dataDifference->getItemsToCreate($scopeData);
            if ($itemsToCreate) {
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
