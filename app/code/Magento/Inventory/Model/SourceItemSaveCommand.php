<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Inventory\Model\ResourceModel\SourceItem as ResourceSource;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemSaveCommandInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SourceItemSave
 */
class SourceItemSaveCommand implements SourceItemSaveCommandInterface
{
    /**
     * @var ResourceSource
     */
    private $resourceSource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SourceItemMultipleSave constructor
     *
     * @param ResourceSource $resourceSource
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceSource $resourceSource,
        LoggerInterface $logger
    ) {
        $this->resourceSource = $resourceSource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceItems)
    {
        try {
            $sourceItemsData = [];

            /** @var SourceItemInterface $sourceItem */
            foreach ($sourceItems as $sourceItem) {
                $sourceItemsData[] = [
                    SourceItemInterface::SOURCE_ITEM_ID => $sourceItem->getSourceItemId(),
                    SourceItemInterface::SOURCE_ID => $sourceItem->getSourceId(),
                    SourceItemInterface::SKU => $sourceItem->getSku(),
                    SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                    SourceItemInterface::STATUS => $sourceItem->getStatus(),
                ];
            }
            $this->resourceSource->multipleSave($sourceItemsData);

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save source item'), $e);
        }
    }
}
