<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Inventory\Model\ResourceModel\SourceStockLink as ResourceSourceStockLink;
use Magento\InventoryApi\Api\Data\SourceStockLinkInterface;
use Magento\InventoryApi\Api\Data\SourceStockLinkInterfaceFactory as SourceStockLinkFactory;
use Magento\InventoryApi\Api\SourceStockLinkRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Model to assign specific source to particular stock.
 */
class SourceStockLinkRepository implements SourceStockLinkRepositoryInterface
{

    /**
     * @var SourceStockLinkFactory
     */
    private $sourceStockLinkFactory;

    /**
     * @var ResourceSourceStockLink
     */
    private $resourceSourceStockLink;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ResourceSourceStockLink $resourceSourceStockLink
     * @param LoggerInterface $logger
     * @param SourceStockLinkFactory $sourceStockLinkFactory
     */
    public function __construct(
        ResourceSourceStockLink $resourceSourceStockLink,
        LoggerInterface $logger,
        SourceStockLinkFactory $sourceStockLinkFactory
    ) {
        $this->resourceSourceStockLink = $resourceSourceStockLink;
        $this->logger = $logger;
        $this->sourceStockLinkFactory = $sourceStockLinkFactory;
    }

    /**
     * @inheritdoc
     */
    public function get($linkId)
    {
        $sourceStockLink= $this->sourceStockLinkFactory->create();
        $this->resourceSourceStockLink->load($sourceStockLink, $linkId, SourceStockLinkInterface::LINK_ID);

        /** @var SourceStockLinkInterface $sourceStockLink */
        if (null === $sourceStockLink->getLinkId()) {
            throw NoSuchEntityException::singleField(SourceStockLinkInterface::LINK_ID, $linkId);
        }

        return $sourceStockLink;
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
            throw new CouldNotSaveException(__('Could not save Source Stock Link'), $e);
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

    /**
     * @inheritdoc
     */
    public function deleteById($linkId)
    {
        $sourceStockLink = $this->getById($linkId);
        $this->delete($sourceStockLink);
    }
}
