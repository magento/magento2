<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\Stock\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Model\StockValidatorInterface;
use Magento\InventorySales\Model\ResourceModel\StockIdResolver;
use Magento\InventorySales\Model\SalesChannel;
use Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface;

class WebsiteAssignedToStockValidator implements StockValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var GetAssignedSalesChannelsForStockInterface
     */
    private $getAssignedSalesChannelsForStock;

    /**
     * @var StockIdResolver
     */
    private $stockIdResolver;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
     * @param StockIdResolver $stockIdResolver
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock,
        StockIdResolver $stockIdResolver
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->getAssignedSalesChannelsForStock = $getAssignedSalesChannelsForStock;
        $this->stockIdResolver = $stockIdResolver;
    }

    /**
     * @inheritdoc
     */
    public function validate(StockInterface $stock): ValidationResult
    {
        $errors = [];
        $unAssignedWebsiteCodes = $this->getUnassignedWebsiteCodesForStock($stock);

        foreach ($unAssignedWebsiteCodes as $websiteCode) {
            $stockId = (int)$this->stockIdResolver->resolve(SalesChannel::TYPE_WEBSITE, $websiteCode);

            if ($stockId === (int)$stock->getStockId() || $stockId === null) {
                $errors[] = __('Website "%field" should be linked to stock.', ['field' => $websiteCode]);
            }
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }

    /**
     * @param StockInterface $stock
     * @return array
     */
    private function getUnassignedWebsiteCodesForStock(StockInterface $stock): array
    {
        $assignedWebsiteCodes = $newWebsiteCodes = [];
        $assignedSalesChannels = $this->getAssignedSalesChannelsForStock->execute((int)$stock->getStockId());

        foreach ($assignedSalesChannels as $salesChannel) {
            if ($salesChannel->getType() === SalesChannel::TYPE_WEBSITE) {
                $assignedWebsiteCodes[] = $salesChannel->getCode();
            }
        }

        $extensionAttributes = $stock->getExtensionAttributes();
        $newSalesChannels = $extensionAttributes->getSalesChannels() ?: [];

        foreach ($newSalesChannels as $salesChannel) {
            if ($salesChannel->getType() === SalesChannel::TYPE_WEBSITE) {
                $newWebsiteCodes[] = $salesChannel->getCode();
            }
        }

        return array_diff($assignedWebsiteCodes, $newWebsiteCodes);
    }
}
