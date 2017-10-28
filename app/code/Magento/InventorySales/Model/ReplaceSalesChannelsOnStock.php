<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\StockSourceLink\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Inventory\Model\ResourceModel\StockSourceLink\SaveMultiple;
use Magento\InventorySales\Model\SalesChannel;
use Magento\InventorySalesApi\Api\AssignSalesChannelsToStockInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class AssignSalesChannelsToStock implements AssignSalesChannelsToStockInterface
{
    /**
     * @var SaveMultiple
     */
    private $saveMultiple;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SaveMultiple $saveMultiple
     * @param LoggerInterface $logger
     */
    public function __construct(
        SaveMultiple $saveMultiple,
        LoggerInterface $logger
    ) {
        $this->saveMultiple = $saveMultiple;
        $this->logger = $logger;
    }

    /**
     * Assign Sources to Stock
     *
     * If one of the Sources or Stock with given id don't exist then exception will be throw
     *
     * @param SalesChannel[] $salesChannels
     * @param int $stockId
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(array $salesChannels, int $stockId);
    {
    try{

}
        try {
            $this->saveMultiple->execute($salesChannels, $stockId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not assign SalesChannels to Stock'), $e);
        }
    }
}
