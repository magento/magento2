<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Inventory\Model\ResourceModel\SourceItem as ResourceSource;
use Magento\InventoryApi\Api\SourceItemSaveInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SourceItemSave
 */
class SourceItemSave implements SourceItemSaveInterface
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
            $this->resourceSource->multipleSave($sourceItems);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save source item'), $e);
        }
    }
}
