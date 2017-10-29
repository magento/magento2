<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\SourceItemConfiguration;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration as SourceItemConfigurationResourceModel;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class Delete implements DeleteInterface
{
    /**
     * @var SourceItemConfigurationResourceModel
     */
    private $sourceItemConfigurationResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SourceItemConfigurationResourceModel $sourceItemConfigurationResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        SourceItemConfigurationResourceModel $sourceItemConfigurationResource,
        LoggerInterface $logger
    ) {
        $this->sourceItemConfigurationResource = $sourceItemConfigurationResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function delete(SourceItemConfigurationInterface $sourceItemConfiguration)
    {
        try {
            $this->sourceItemConfigurationResource->delete($sourceItemConfiguration);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete SourceItem Configuration.'), $e);
        }
    }
}
