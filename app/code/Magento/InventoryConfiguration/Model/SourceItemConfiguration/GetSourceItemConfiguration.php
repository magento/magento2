<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\SourceItemConfiguration;

use Magento\InventoryConfiguration\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfiguration\Api\GetSourceItemConfigurationInterface;
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration\GetSourceItemConfiguration as ResourceGetSourceItemConfiguration;
use Psr\Log\LoggerInterface;


/**
 * Implementation of SourceItem Quantity notification save multiple operation for specific db layer
 * Save Multiple used here for performance efficient purposes over single save operation
 */
class GetSourceItemConfiguration implements GetSourceItemConfigurationInterface
{

    /**
     * @var ResourceGetSourceItemConfiguration
     */
    protected $getConfiguration;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ResourceGetSourceItemConfiguration constructor.
     *
     * @param ResourceGetSourceItemConfiguration $getConfiguration
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceGetSourceItemConfiguration $getConfiguration,
        LoggerInterface $logger
    )
    {
        $this->getConfiguration = $getConfiguration;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getSourceItemConfiguration(string $sourceId, string $sku): SourceItemConfigurationInterface
    {
        return $this->getConfiguration->execute($sourceId, $sku);
    }
}