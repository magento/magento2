<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Stock\Command;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Inventory\Model\ResourceModel\Stock as StockResourceModel;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;

/**
 * @inheritdoc
 */
class Get implements GetInterface
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
     * @param StockResourceModel $stockResource
     * @param StockInterfaceFactory $stockFactory
     */
    public function __construct(
        StockResourceModel $stockResource,
        StockInterfaceFactory $stockFactory
    ) {
        $this->stockResource = $stockResource;
        $this->stockFactory = $stockFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $stockId): StockInterface
    {
        /** @var StockInterface $stock */
        $stock = $this->stockFactory->create();
        $this->stockResource->load($stock, $stockId, StockInterface::STOCK_ID);

        if (null === $stock->getStockId()) {
            throw new NoSuchEntityException(__('Stock with id "%value" does not exist.', ['value' => $stockId]));
        }
        return $stock;
    }
}
