<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Get configuration values related to approximate number of products calculation for grid.
 */
class CalculateApproximateProductsNumber implements ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if configuration setting to calculate approximate total number of products in grid is enabled.
     *
     * @return bool
     */
    public function calculateApproximateProductsTotalNumber(): bool
    {
        return (bool)$this->scopeConfig->getValue('admin/grid/calculate_approximate_total_number_of_products');
    }

    /**
     * Get records threshold for approximate total number of products calculation.
     *
     * @return int
     */
    public function getRecordsThreshold(): int
    {
        return (int)$this->scopeConfig->getValue('admin/grid/records_threshold');
    }
}
