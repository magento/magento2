<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class StockDataFilter
 * @since 2.0.0
 */
class StockDataFilter
{
    /**
     * The greatest value which could be stored in CatalogInventory Qty field
     */
    const MAX_QTY_VALUE = 99999999;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var StockConfigurationInterface
     * @since 2.0.0
     */
    protected $stockConfiguration;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StockConfigurationInterface $stockConfiguration
     * @since 2.0.0
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
     * @since 2.0.0
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

        if (isset($stockData['min_qty']) && (int)$stockData['min_qty'] < 0) {
            $stockData['min_qty'] = 0;
        }

        if (!isset($stockData['is_decimal_divided']) || $stockData['is_qty_decimal'] == 0) {
            $stockData['is_decimal_divided'] = 0;
        }

        return $stockData;
    }
}
