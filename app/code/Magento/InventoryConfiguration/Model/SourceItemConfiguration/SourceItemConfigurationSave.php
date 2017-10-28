<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryConfiguration\Model\SourceItemConfiguration;

use Magento\InventoryConfiguration\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration\SaveSourceItemConfiguration as SaveSourceItemConfigurationModel;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\InventoryConfiguration\Api\SourceItemConfigurationSaveInterface;
use Psr\Log\LoggerInterface;


/**
 * Implementation of SourceItem Quantity notification save multiple operation for specific db layer
 * Save Multiple used here for performance efficient purposes over single save operation
 */
class SourceItemConfigurationSave implements SourceItemConfigurationSaveInterface
{

    /**
     * @var SaveSourceItemConfigurationModel
     */
    protected $saveConfiguration;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SaveSourceItemConfiguration constructor.
     *
     * @param SaveSourceItemConfigurationModel $saveConfiguration
     * @param LoggerInterface $logger
     */
    public function __construct(
        SaveSourceItemConfigurationModel $saveConfiguration,
        LoggerInterface $logger
    )
    {
        $this->saveConfiguration = $saveConfiguration;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function saveSourceItemConfiguration(array $configuration)
    {
        if (empty($configuration)) {
            throw new InputException(__('Input data is empty'));
        }
        try {
            $this->saveConfiguration->execute($configuration);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Source Item'), $e);
        }
    }
}