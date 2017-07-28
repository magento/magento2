<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Stock\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Inventory\Model\ResourceModel\Stock as StockResourceModel;
use Magento\InventoryApi\Api\Data\StockInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class Save implements SaveInterface
{
    /**
     * @var StockResourceModel
     */
    private $stockResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StockResourceModel $stockResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockResourceModel $stockResource,
        LoggerInterface $logger
    ) {
        $this->stockResource = $stockResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(StockInterface $stock)
    {
        try {
            $this->stockResource->save($stock);
            return $stock->getStockId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Stock'), $e);
        }
    }
}
