<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\IsSalableCondition;

use Magento\CatalogInventory\Api\StockConfigurationInterface;

/**
 * Condition for manage_stock configuration.
 */
class GetManageStockCondition implements GetIsSalableConditionInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $configuration;

    /**
     * @param StockConfigurationInterface $configuration
     */
    public function __construct(StockConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritdoc
     */
    public function execute(): string
    {
        $globalManageStock = (int)$this->configuration->getManageStock();

        $condition = '(
            (config.use_config_manage_stock = 1 AND ' . $globalManageStock . ' = 0)
            OR 
            (config.use_config_manage_stock = 0 AND config.manage_stock = 0)
        )';

        return $condition;
    }
}
