<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Inventory\Model\ResourceModel\SourceStockLink as ResourceSourceStockLink;
use Magento\InventoryApi\Api\SourceStockLinkRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Model to assign specific source to particular stock.
 */
class SourceStockLinkRepository implements SourceStockLinkRepositoryInterface
{
    /**
     * @var ResourceSourceStockLink
     */
    private $resourceSourceStockLink;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SourceStockLinkRepository constructor.
     * @param ResourceSourceStockLink $resourceSourceStockLink
     * @param LoggerInterface $logger
     */
    public function __construct(ResourceSourceStockLink $resourceSourceStockLink, LoggerInterface $logger)
    {
        $this->resourceSourceStockLink = $resourceSourceStockLink;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink)
    {
        try {
            $this->resourceSourceStockLink->save($sourceStockLink);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not delete Source Stock Link'), $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function delete(\Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink)
    {
        try {
            $this->resourceSourceStockLink->delete($sourceStockLink);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete Source Stock Link'), $e);
        }
    }
}
