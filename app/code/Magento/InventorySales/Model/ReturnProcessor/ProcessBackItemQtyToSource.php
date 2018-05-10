<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\InventorySales\Model\ReturnProcessor\Request\BackItemQtyRequestInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventoryShipping\Model\GetSourceItemBySourceCodeAndSku;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

class ProcessBackItemQtyToSource
{
    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetSourceItemBySourceCodeAndSku
     */
    private $getSourceItemBySourceCodeAndSku;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @param StockResolverInterface $stockResolver
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku
     * @param SourceItemsSaveInterface $sourceItemsSave
     */
    public function __construct(
        StockResolverInterface $stockResolver,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku,
        SourceItemsSaveInterface $sourceItemsSave
    ) {
        $this->stockResolver = $stockResolver;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getSourceItemBySourceCodeAndSku = $getSourceItemBySourceCodeAndSku;
        $this->sourceItemsSave = $sourceItemsSave;
    }

    /**
     * @param BackItemQtyRequestInterface $backItemQtyRequest
     * @param SalesChannelInterface $salesChannel
     * @return void
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(
        BackItemQtyRequestInterface $backItemQtyRequest,
        SalesChannelInterface $salesChannel
    ) {
        $backQty = $backItemQtyRequest->getQuantity();
        if ($backQty <= 0) {
            return;
        }

        $stockId = (int)$this->stockResolver->get($salesChannel->getType(), $salesChannel->getCode())->getStockId();

        $stockItemConfiguration = $this->getStockItemConfiguration->execute(
            $backItemQtyRequest->getSku(),
            $stockId
        );

        if (!$stockItemConfiguration->isManageStock()) {
            return;
        }

        $sourceItem = $this->getSourceItemBySourceCodeAndSku->execute(
            $backItemQtyRequest->getSourceCode(),
            $backItemQtyRequest->getSku()
        );
        $sourceItem->setQuantity($sourceItem->getQuantity() + $backQty);
        $this->sourceItemsSave->execute([$sourceItem]);
    }
}
