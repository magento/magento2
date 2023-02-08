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
 * Get configuration values related to limit total number of products in grid collection.
 */
class LimitTotalNumberOfProductsInGrid implements ArgumentInterface
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
     * Check if configuration setting to limit total number of products in grid is enabled.
     *
     * @return bool
     */
    public function limitTotalNumberOfProducts(): bool
    {
        return (bool)$this->scopeConfig->getValue('admin/grid/limit_total_number_of_products');
    }

    /**
     * Get records threshold for limit total number of products in collection.
     *
     * @return int
     */
    public function getRecordsLimit(): int
    {
        return (int)$this->scopeConfig->getValue('admin/grid/records_limit');
    }
}
