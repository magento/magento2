<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration\Command;

use Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration\ConfigValueProvider;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * Get default configuration values from system config
 */
class GetDefaultValues
{
    /**
     * @var ConfigValueProvider
     */
    private $configValueProvider;

    /**
     * @param ConfigValueProvider $configValueProvider
     */
    public function __construct(
        ConfigValueProvider $configValueProvider
    ) {
        $this->configValueProvider = $configValueProvider;
    }

    /**
     * @param string $sourceCode
     * @param string $sku
     * @return array
     */
    public function execute(string $sourceCode, string $sku): array
    {
        $inventoryNotifyQty = $this->configValueProvider->execute('notify_stock_qty');

        $defaultConfiguration = [
            SourceItemConfigurationInterface::SOURCE_CODE => $sourceCode,
            SourceItemConfigurationInterface::SKU => $sku,
            SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => $inventoryNotifyQty,
        ];

        return $defaultConfiguration;
    }
}
