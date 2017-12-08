<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\SourceItemConfiguration\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * Get default configuration values from system config
 */
class GetDefaultValues
{
    /**
     * Default Notify Stock Qty config path
     */
    const XML_PATH_NOTIFY_STOCK_QTY = 'inventory/source_item_configuration/notify_stock_qty';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int $sourceId
     * @param string $sku
     * @return array
     */
    public function execute(int $sourceId, string $sku) : array
    {
        $inventoryNotifyQty = (float)$this->scopeConfig->getValue(self::XML_PATH_NOTIFY_STOCK_QTY);

        $defaultConfiguration = [
            SourceItemConfigurationInterface::SOURCE_ID => $sourceId,
            SourceItemConfigurationInterface::SKU => $sku,
            SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => $inventoryNotifyQty,
        ];
        return $defaultConfiguration;
    }
}
