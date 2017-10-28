<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryConfiguration\Model\SourceItemConfiguration;

use Magento\InventoryConfiguration\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration\SaveSourceItemConfiguration as SaveSourceItemConfigurationModel;
use Magento\InventoryConfiguration\Api\GetSourceItemConfigurationInterface;
use Psr\Log\LoggerInterface;


/**
 * Implementation of SourceItem Quantity notification save multiple operation for specific db layer
 * Save Multiple used here for performance efficient purposes over single save operation
 */
class GetSourceItemConfiguration implements GetSourceItemConfigurationInterface
{

    /**
     * @var SaveSourceItemConfigurationModel
     */
    protected $getConfiguration;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SaveSourceItemConfiguration constructor.
     *
     * @param GetSourceItemConfiguration $getConfiguration
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetSourceItemConfiguration $getConfiguration,
        LoggerInterface $logger
    )
    {
        $this->getConfiguration = $getConfiguration;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getSourceItemConfiguration(int $sourceId, string $sku): SourceItemConfigurationInterface
    {
        return $this->getConfiguration->execute($sourceId, $sku);
    }
}