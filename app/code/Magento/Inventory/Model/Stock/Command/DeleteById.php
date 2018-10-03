<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Stock\Command;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Inventory\Model\ResourceModel\Stock as StockResourceModel;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class DeleteById implements DeleteByIdInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StockResourceModel $stockResource
     * @param StockInterfaceFactory $stockFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockResourceModel $stockResource,
        StockInterfaceFactory $stockFactory,
        LoggerInterface $logger
    ) {
        $this->stockResource = $stockResource;
        $this->stockFactory = $stockFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $stockId)
    {
        /** @var StockInterface $stock */
        $stock = $this->stockFactory->create();
        $this->stockResource->load($stock, $stockId, StockInterface::STOCK_ID);

        if (null === $stock->getStockId()) {
            throw new NoSuchEntityException(
                __(
                    'There is no stock with "%fieldValue" for "%fieldName". Verify and try again.',
                    [
                        'fieldName' => StockInterface::STOCK_ID,
                        'fieldValue' => $stockId
                    ]
                )
            );
        }

        try {
            $this->stockResource->delete($stock);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete Stock'), $e);
        }
    }
}
