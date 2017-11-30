<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\SourceItemConfiguration;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration\DeleteSourceItemConfiguration;
use Psr\Log\LoggerInterface;
use Magento\InventoryConfigurationApi\Api\DeleteSourceItemConfigurationInterface;

/**
 * @inheritdoc
 */
class Delete implements DeleteSourceItemConfigurationInterface
{
    /**
     * @var DeleteSourceItemConfiguration
     */
    private $deleteSourceItemConfiguration;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeleteSourceItemConfiguration $deleteSourceItemConfiguration
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteSourceItemConfiguration $deleteSourceItemConfiguration,
        LoggerInterface $logger
    ) {
        $this->deleteSourceItemConfiguration = $deleteSourceItemConfiguration;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $sourceId, string $sku)
    {
        try {
            $this->deleteSourceItemConfiguration->execute($sourceId, $sku);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete SourceItem Configuration.'), $e);
        }
    }
}
