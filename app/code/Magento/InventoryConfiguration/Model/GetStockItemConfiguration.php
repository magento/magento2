<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

/**
 * Service to return  stock item configuration interface object
 */
class GetStockItemConfiguration implements GetStockItemConfigurationInterface
{
    /**
     * @var StockItemConfigurationFactory
     */
    private $stockItemConfigurationFactory;

    /**
     * @param StockItemConfigurationFactory $stockItemConfigurationFactory
     */
    public function __construct(
        StockItemConfigurationFactory $stockItemConfigurationFactory
    ) {
        $this->stockItemConfigurationFactory = $stockItemConfigurationFactory;
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return StockItemConfigurationInterface
     */
    public function execute(string $sku, int $stockId): StockItemConfigurationInterface
    {
        return $this->stockItemConfigurationFactory->create($sku, $stockId);
    }
}
