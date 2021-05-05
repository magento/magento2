<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model;

use Magento\Checkout\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provides configuration values for PayPal PayLater Banners
 */
class PayLaterConfig
{
    /**
     * Configuration key for Styles settings
     */
    const CONFIG_KEY_STYLES = 'styles';

    /**
     * Configuration key for Position setting
     */
    const CONFIG_KEY_POSITION = 'position';

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
     * Check if Banner enabled for specified page
     *
     * @param string $placement
     * @return bool
     */
    public function isEnabled(string $placement)
    {
        $isPayLaterEnabled = true; //read from config
        return $isPayLaterEnabled && $this->getSectionConfig($placement, 'display');
    }

    /**
     * Get config for a specific section and key
     *
     * @param string $section
     * @param string $key
     * @return array|mixed
     */
    public function getSectionConfig($section, $key)
    {
        $configMock = [
            'product' => [
                'display' => 1,
                'position' => 'header', // 'sidebar'
                'style' => [
                    'data-pp-style-logo-position' => 'right'
                ]
            ],
        ];
        return $configMock[$section][$key] ?? [];
    }
}
