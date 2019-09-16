<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Model\Stock;

/**
 * Class StockDataFilter
 */
class StockDataFilter
{
    /**
     * The greatest value which could be stored in CatalogInventory Qty field
     */
    const MAX_QTY_VALUE = 99999999;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Filter stock data
     *
     * @param array $stockData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function filter(array $stockData)
    {
        if (!isset($stockData['use_config_manage_stock'])) {
            $stockData['use_config_manage_stock'] = 0;
        }

        if ($stockData['use_config_manage_stock'] == 1 && !isset($stockData['manage_stock'])) {
            $stockData['manage_stock'] = $this->stockConfiguration->getManageStock();
        }
        if (isset($stockData['qty']) && (double)$stockData['qty'] > self::MAX_QTY_VALUE) {
            $stockData['qty'] = self::MAX_QTY_VALUE;
        }

        if (isset($stockData['min_qty'])) {
            $stockData['min_qty'] = $this->purifyMinQty($stockData['min_qty'], $stockData['backorders']);
        }

        if (!isset($stockData['is_decimal_divided']) || $stockData['is_qty_decimal'] == 0) {
            $stockData['is_decimal_divided'] = 0;
        }

        return $stockData;
    }

    /**
     * Purifies min_qty.
     *
     * @param int $minQty
     * @param int $backOrders
     * @return float
     */
    private function purifyMinQty(int $minQty, int $backOrders): float
    {
        /**
         * As described in the documentation if the Backorders Option is disabled
         * it is recommended to set the Out Of Stock Threshold to a positive number.
         * That's why to clarify the logic to the end user the code below prevent him to set a negative number so such
         * a number will turn to zero.
         * @see https://docs.magento.com/m2/ce/user_guide/catalog/inventory-backorders.html
         */
        if ($backOrders === Stock::BACKORDERS_NO && $minQty < 0) {
            $minQty = 0;
        }

        return (float)$minQty;
    }
}
