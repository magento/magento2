<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Inventory\Model\ResourceModel\SourceStockLink as SourceStockLinkResourceModel;
use Magento\InventoryApi\Api\Data\SourceStockLinkInterface;
use Magento\InventoryApi\Api\SourceStockLinkRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class SourceStockLinkRepository implements SourceStockLinkRepositoryInterface
{
    /**
     * @var SourceStockLinkResourceModel
     */
    private $sourceStockLinkResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SourceStockLinkResourceModel $sourceStockLinkResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        SourceStockLinkResourceModel $sourceStockLinkResource,
        LoggerInterface $logger
    ) {
        $this->sourceStockLinkResource = $sourceStockLinkResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function save(SourceStockLinkInterface $sourceStockLink)
    {
        try {
            $this->sourceStockLinkResource->save($sourceStockLink);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Source Stock Link'), $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function delete(SourceStockLinkInterface $sourceStockLink)
    {
        try {
            $this->sourceStockLinkResource->delete($sourceStockLink);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete Source Stock Link'), $e);
        }
    }
}
