<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Inventory\Model\ResourceModel\Stock as StockResourceModel;
use Magento\Inventory\Model\StockRepository\GetList;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class StockRepository implements StockRepositoryInterface
{
    /**
     * @var StockResourceModel
     */
    private $stockResource;

    /**
     * @var StockInterfaceFactory
     */
    private $stockFactory;

    /**
     * @var GetList
     */
    private $getList;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SourceRepository constructor
     *
     * @param StockResourceModel $stockResource
     * @param StockInterfaceFactory $stockFactory
     * @param GetList $getList
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockResourceModel $stockResource,
        StockInterfaceFactory $stockFactory,
        GetList $getList,
        LoggerInterface $logger
    ) {
        $this->stockResource = $stockResource;
        $this->stockFactory = $stockFactory;
        $this->getList = $getList;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function save(StockInterface $stock)
    {
        try {
            $this->stockResource->save($stock);
            return $stock->getStockId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Stock'), $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function get($stockId)
    {
        $stock = $this->stockFactory->create();
        $this->stockResource->load($stock, $stockId, StockInterface::STOCK_ID);

        if (null === $stock->getStockId()) {
            throw NoSuchEntityException::singleField(StockInterface::STOCK_ID, $stockId);
        }
        return $stock;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($stockId)
    {
        $stockItem = $this->get($stockId);

        try {
            $this->stockResource->delete($stockItem);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete Stock'), $e);
        }

    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this->getList->execute($searchCriteria);
    }
}
