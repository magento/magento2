<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration\DeleteMultiple
    as DeleteResourceModel;
use Magento\InventoryLowQuantityNotificationApi\Api\DeleteSourceItemsConfigurationInterface;
use Psr\Log\LoggerInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\DeleteSourceItemConfigurationInterface;

/**
 * @inheritdoc
 */
class DeleteMultiple implements DeleteSourceItemsConfigurationInterface
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
     * DeleteMultiple constructor.
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
    public function execute(array $sourceItems): void
    {
        try {
            $this->deleteResourceModel->execute($sourceItems);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete SourceItems Configuration.'), $e);
        }
    }
}
