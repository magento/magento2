<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration\Command;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration\Delete as DeleteResourceModel;
use Psr\Log\LoggerInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\DeleteSourceItemConfigurationInterface;

/**
 * @inheritdoc
 */
class Delete implements DeleteSourceItemConfigurationInterface
{
    /**
     * @var DeleteResourceModel
     */
    private $deleteResourceModel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeleteResourceModel $deleteResourceModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteResourceModel $deleteResourceModel,
        LoggerInterface $logger
    ) {
        $this->deleteResourceModel = $deleteResourceModel;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sourceCode, string $sku)
    {
        try {
            $this->deleteResourceModel->execute($sourceCode, $sku);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete SourceItem Configuration.'), $e);
        }
    }
}
