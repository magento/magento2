<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\SourceItemConfiguration;

use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetSourceItemConfigurationInterface;
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration\GetSourceItemConfiguration
    as ResourceGetSourceItemConfiguration;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
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
    private $getConfiguration;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ResourceGetSourceItemConfiguration constructor.
     *
     * @param ResourceGetSourceItemConfiguration $getConfiguration
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceGetSourceItemConfiguration $getConfiguration,
        LoggerInterface $logger
    ) {
        $this->getConfiguration = $getConfiguration;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function get(int $sourceId, string $sku): SourceItemConfigurationInterface
    {
        if (empty($sourceId) || empty($sku)) {
            throw new InputException(__('SourceId oder Sku missing'));
        }
        try {
            return $this->getConfiguration->execute($sourceId, $sku);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new LocalizedException(__('Could not load Source Item Configuration.'), $e);
        }
    }
}
