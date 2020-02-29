<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * View model for dashboard chart disabled notice
 */
class ChartDisabled implements ArgumentInterface
{
    /**
     * Location of the "Enable Chart" config param
     */
    private const XML_PATH_ENABLE_CHARTS = 'admin/dashboard/enable_charts';

    /**
     * Route to Stores -> Configuration section
     */
    private const ROUTE_SYSTEM_CONFIG = 'adminhtml/system_config/edit';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get url to dashboard chart configuration
     *
     * @return string
     */
    public function getConfigUrl(): string
    {
        return $this->urlBuilder->getUrl(
            static::ROUTE_SYSTEM_CONFIG,
            ['section' => 'admin', '_fragment' => 'admin_dashboard-link']
        );
    }

    /**
     * Check if dashboard chart is enabled
     *
     * @return bool
     */
    public function isChartEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_ENABLE_CHARTS,
            ScopeInterface::SCOPE_STORE
        );
    }
}
