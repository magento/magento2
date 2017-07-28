<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\SourceItem\Command;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class Delete implements DeleteInterface
{
    /**
     * @var SourceItemResourceModel
     */
    private $sourceItemResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SourceItemResourceModel $sourceItemResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        SourceItemResourceModel $sourceItemResource,
        LoggerInterface $logger
    ) {
        $this->sourceItemResource = $sourceItemResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(SourceItemInterface $sourceItem)
    {
        try {
            $this->sourceItemResource->delete($sourceItem);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete SourceItem'), $e);
        }
    }
}
