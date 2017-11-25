<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\SourceItemConfiguration;

use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration\SaveSourceItemConfiguration as SaveSourceItemConfigurationModel;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\InventoryConfigurationApi\Api\SourceItemConfigurationsSaveInterface;
use Psr\Log\LoggerInterface;


/**
 * Implementation of SourceItem Quantity notification save multiple operation for specific db layer
 * Save Multiple used here for performance efficient purposes over single save operation
 */
class SourceItemConfigurationsSave implements SourceItemConfigurationsSaveInterface
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
    public function execute(array $configuration)
    {
        if (empty($configuration)) {
            throw new InputException(__('Input data is empty'));
        }
        try {
            $this->saveConfiguration->execute($configuration);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Source Item Configuration'), $e);
        }
    }
}